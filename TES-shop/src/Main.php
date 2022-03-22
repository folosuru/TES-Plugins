<?php

declare(strict_types=1);

namespace folosuru\TES_shop;

use folosuru\TES;
use bbo51dog\bboform\element\Button;
use bbo51dog\bboform\element\ClosureButton;
use bbo51dog\bboform\element\Dropdown;
use bbo51dog\bboform\element\Input;
use bbo51dog\bboform\element\Label;
use bbo51dog\bboform\element\Slider;
use bbo51dog\bboform\element\StepSlider;
use bbo51dog\bboform\element\Toggle;
use bbo51dog\bboform\form\ClosureCustomForm;
use bbo51dog\bboform\form\CustomForm;
use bbo51dog\bboform\form\SimpleForm;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\block\utils\SignText;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use SQLite3;


class Main extends PluginBase implements Listener {


	private $shopdata;
	private $sqlite;
	/**
	 * @var \folosuru\item_storage\Main
	 */
	private $itemstorage;

	/**
	 * @var TES\Main
	 */
	private $TES_main;

	public function onEnable(): void{
		$this->TES_main = TES\Main::getInstance();
		$this->itemstorage = \folosuru\item_storage\Main::getInstance();
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->sqlite = new SQLite3($this->getDataFolder().'shop.sqlite');
	}

	public function onSignChange(SignChangeEvent $event){
		$pos = $event->getBlock()->getPosition()->getFloorX()."-".$event->getBlock()->getPosition()->getFloorY()."-".$event->getBlock()->getPosition()->getFloorZ();
		if ($event->getNewText()->getLine(0) == "signshop"){

			if (!is_numeric($amount = $event->getNewText()->getLine(1))){
				if ($amount){ return; }else{ $amount = 1 ;}# 数字じゃなくて空文字でもない場合return、空文字なら1にする
			}
			if (!$this->TES_main->existCurrency($currency = $event->getNewText()->getLine(2))){
				$currency = TextFormat::RED.$currency;
			}

			if (!is_numeric($price = $event->getNewText()->getLine(3))) {
				if ($price) {return;}else{ $price =0 ;}
			}


			$this->shopdata[$pos]= array(
				"Owner" => $event->getPlayer()->getName(),
				"amount" => $amount,
				"currency" => $currency = $event->getNewText()->getLine(2),
				"price" => $price,
				"item" => "",
				"meta" => "",
				"name" => "empty",
				"existCurrency" => $this->TES_main->existCurrency($event->getNewText()->getLine(2)),
				"isEnable" => true
			);
			$event->setNewText(new SignText([
					"signshop",
					"商品が設定されていません",
					$event->getNewText()->getLine(1)."個",
					$event->getNewText()->getLine(3)." ".$currency
			]));
			$task = new sqlWriteTask($this->sqlite,$pos,$this->shopdata[$pos]);
			$this->getServer()->getAsyncPool()->submitTask($task);

		}
	}

	public function onBlockTouch(PlayerInteractEvent $event){
		$pos = $event->getBlock()->getPosition()->getFloorX()."-".$event->getBlock()->getPosition()->getFloorY()."-".$event->getBlock()->getPosition()->getFloorZ();
		if (isset($this->shopdata[$pos])) {
			$event->cancel();
			$shop = $this->shopdata[$pos];
			$player = $event->getPlayer();
			if ($player->getName() != $shop["Owner"]) {
				if ($shop["item"] === "商品が設定されていません" or $this->TES_main->existCurrency($shop["currency"]) == false) {
					$player->sendMessage("このSHOPはまだ準備中です。");
					return;
				}
				if ($event->getAction() == 0) return;
				$storage =  $this->itemstorage->GetStorage($shop["owner"]);
				$item = new Item(new ItemIdentifier($shop["id"],$shop["meta"]));
				$item->setCount($shop["amount"]);
				if  (!$storage->canRemoveItem($item)){
					$player->sendMessage("在庫がありません");
					return;
				}
				$playerdata = $this->TES_main->getPlayerData($player);
				if ($playerdata->getPlayerMoney($shop["currency"]) < $shop["price"]){
					$player->sendMessage("お金が足りません");
					return;
				}
				$item =  (new Item(new ItemIdentifier($shop["id"],$shop["meta"])))
					->setCount($shop["amount"]);
				if ($player->getInventory()->canAddItem($item)){
					$player->getInventory()->addItem($item);
					$storage->removeItem($item);
					$playerdata->removeMoney($shop['currency'],$shop['price']);
					$this->TES_main->getPlayerData($shop['owner'])->addPlayerMoney($shop['currency'],$shop['price']);
				}else{
					$player->sendMessage('インベントリに空きがありません');
				}
			} else {
				if ($event->getAction() == 0) return;
				if ($player->isSneaking()) {
					$shop["item"] = $player->getInventory()->getItemInHand()->getId();
					$shop["meta"] = $player->getInventory()->getItemInHand()->getMeta();
					$shop["name"] = $player->getInventory()->getItemInHand()->getName();
					$this->editshop($event->getBlock(),$shop);
				}else{
					$amount_input = new Input("個数","1～",(string)$shop["amount"]);
					$currency_input = new Input("通貨","",(string)$shop["currency"]);
					$price_input = new Input("価格","0～", (string)$shop["price"]);
					$chkbox = new Toggle("SHOPのオン/オフ", $shop["isEnable"]);

					$form = (new ClosureCustomForm(function (Player $player, CustomForm $form) use($amount_input,$price_input,$currency_input,$chkbox,$shop,$event) {
						if (!$this->TES_main->existCurrency($currency_input->getValue())){
							$player->sendMessage("[signshop] ".$currency_input->getValue()."という通貨は存在しません");
						}else{
							$shop["currency"] = $currency_input->getValue();
						}

						if (is_numeric($amount_input->getValue())){
							if ((int)$amount_input->getValue() > 0 ) {
								$shop["amount"] = (int)$amount_input->getValue();
							}else{$player->sendMessage("[signshop] 個数が0以下です(".$amount_input->getValue().")");}
						} else{
							$player->sendMessage("[signshop] 個数が数字ではありません(".$amount_input->getValue().")");
						}

						if (is_numeric($price_input->getValue())){
							if ($price_input->getValue() >= 0 ) {
								$shop["price"] = (int)$price_input->getValue();
							}else{$player->sendMessage("[signshop] 値段がマイナスか、数字ではありません(".$price_input->getValue().")");}
						} else{
							$player->sendMessage("[signshop] 値段がマイナスか、数字ではありません(".$price_input->getValue().")");
						}
						$this->editshop($event->getBlock(),$shop);
					}))
					->setTitle("Setting")
					->addElements(
						new Label($shop["name"]),
						$amount_input,
						$currency_input,
						$price_input,
						$chkbox
					);
					$event->getPlayer()->sendForm($form);
				}
			}

		}

	}

	private function editshop(Block $Block,array $shop) : void{
		if ($this->TES_main->existCurrency($shop["currency"])) {
			$currency = $shop["currency"];
		}else{
			$currency = TextFormat::RED.$shop["currency"];
		}
		$lines = [
			"signshop",
			$shop["name"],
			$shop["amount"] . "個",
			$shop["price"] . " " . $currency,
		];
		$Block->setText(new SignText($lines));
		$position = $Block->getPosition();
		$position->getWorld()->setBlockAt($position->x, $position->y, $position->z, $Block);
		$this->shopdata[$position->getFloorX()."-". $position->getFloorY()."-".$position->getFloorZ()] = $shop;
		$task = new sqlWriteTask($this->sqlite,$position->getFloorX()."-". $position->getFloorY()."-".$position->getFloorZ(),$shop);
		$this->getServer()->getAsyncPool()->submitTask($task);
	}


}
