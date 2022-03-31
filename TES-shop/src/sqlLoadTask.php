<?php

namespace folosuru\TES_shop;


use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use SQLite3;

class sqlLoadTask extends AsyncTask{

	private string $path;

	public function __construct(string $path){
		$this->path = $path;
	}

	public function onRun(): void{
		$db = new SQLite3($this->path.'shop.sqlite');
		$query =  $db->query('select * from SHOP');
		While ($row = $query->fetchArray(SQLITE3_ASSOC)){
			$result[$row['pos']] = array(
				"pos" => $row['pos'],
				"owner" => $row['owner'],
				"amount" => $row['amount'],
				"currency" => $row['currency'],
				"price" => $row['price'],
				"item" => $row['item'],
				"damage" => $row['damage'],
				"name" => $row['name'],
				"enable"=> $row['enable']
			);
		}
		$this->setResult($result);

	}
	public function onCompletion(): void{
		$server = Server::getInstance();
		$plugin = Main::getInstance();
		$shop = $plugin->shopdata;
		$result = $this->getResult();
		foreach ($result as $key => $value){
			$row = $value;
			if ($row['enable'] == 1){
				$enable = true;
			}else{
				$enable = false;
			}
			if (array_key_exists($row['pos'],$shop)){
				$shop1 = array(
					"Owner" => $row['owner'],
					"amount" => $row['amount'],
					"currency" => $row['currency'],
					"price" => $row['price'],
					"item" => $row['item'],
					"meta" => $row['damage'],
					"name" => $row['name'],
					"isEnable" => $enable
				);
				if ($shop[$row['pos']] == $shop1) return;
				$shop[$row['pos']] = $shop1;
				$pos = explode('_',$row['pos']);
				$block = $server->getWorldManager()->getWorld($pos[0])->getBlock(new Vector3((int)$pos[1],(int)$pos[2],(int)$pos[3]));
				if ($block instanceof BaseSign){
					$lines =  $block->getText()->getLines();
					$line = [
						"signshop",
						$row["name"],
						$row["amount"] . "個",
						$row["price"] . " " .$row['currency'] ,
					];
					if ($lines ==$line){return;}
					$block->setText(new SignText($line));
					$position = $block->getPosition();
					$position->getWorld()->setBlockAt($position->x, $position->y, $position->z, $block);
				}

			}else{
				$shop[$row['pos']] = array(
					"Owner" => $row['owner'],
					"amount" => $row['amount'],
					"currency" => $currency = $row['currency'],
					"price" => $row['price'],
					"item" => $row['item'],
					"meta" => $row['damage'],
					"name" => $row['name'],
					"isEnable" => $enable
				);
			}
		}
		$plugin->shopdata = $shop;
	}


}