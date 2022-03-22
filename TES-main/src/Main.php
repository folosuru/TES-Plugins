<?php

declare(strict_types=1);

namespace folosuru\TES;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


class Main extends PluginBase implements Listener {
	private static $instance;
	private $PlayerData;
	private $CountryData;
	private $DominionData;
	public $setting;
	private $CurrencyData;


	/**
	 * @param Player|string(PlayerName) $player
	 * @return PlayerData
	 */
	public function getPlayerData(Player|string $player) : PlayerData{
		if ($player instanceof Player) $player = $player->getName();
		if (!isset($this->PlayerData[$player])){
			$this->PlayerData[$player] = new PlayerData($this);
		}
		return $this->PlayerData[$player];
	}

	/*public function getCurrencyData(Player $player) : CurrencyData{
		if (!isset($this->CurrencyData[$player->getName()])){
			$this->CurrencyData[$player->getName()] = new CurrencyData($this);
		}
		return $this->CurrencyData[$player->getName()];
	}
*/

	public function existCurrency(string $currency): bool {
		return array_key_exists($currency,$this->CurrencyData);
	}

	public static function getInstance() : Main{
		return self::$instance;
	}

/************************	呼び出すやつ　ここまで	***********************/


	public function onLoad(): void{
		if(!self::$instance instanceof Main){
			self::$instance = $this;
		}
	}

	public function onEnable(): void{
		$this->CountryData = new CountryData($this);
		$this->DominionData = new DominionData($this);
		$this->CurrencyData["ACP"] = new CurrencyData($this);
		$this->setting = array(
			"AreaWidth" => 10,
		);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "setting":
				return true;

			default:
				throw new \Exception('Unexpected value');
		}
	}
	public function onJoin(PlayerJoinEvent $event){

	}





}
