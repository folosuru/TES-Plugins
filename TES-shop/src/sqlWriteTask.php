<?php

namespace folosuru\TES_shop;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use SQLite3;

class sqlWriteTask extends AsyncTask{
	private SQLite3 $SQLite3;
	private array $shop;
	private string $pos;
	private string $path;

	public function __construct(string $path,string $pos , array $shop){
		$this->path = $path;
		$this->pos = $pos;
		$this->shop = $shop;
	}

	public function onRun(): void{
		$sqlite = new SQLite3($this->path.'shop.sqlite');
		if ($this->shop['isEnable'] === true){
			$enable = 1;
		}else{
			$enable = 0;
		}
		$sqlite->exec("delete  from SHOP where pos ='$this->pos'");
		$sql = "insert into SHOP(pos,item,damage,owner,currency,price,amount,enable,name) values('$this->pos',{$this->shop["item"]},{$this->shop["meta"]},'{$this->shop["Owner"]}','{$this->shop["currency"]}',{$this->shop["price"]},{$this->shop["amount"]},{$enable},'{$this->shop['name']}');";
		$this->setResult('b');
		$sqlite->exec($sql);
	}

	public function onCompletion(): void
	{
		parent::onCompletion(); // TODO: Change the autogenerated stub
		Server::getInstance()->getLogger()->info('Saved to SQL');
		Server::getInstance()->getLogger()->info($this->getResult());
	}
}