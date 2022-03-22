<?php

namespace folosuru\TES_shop;

use pocketmine\scheduler\AsyncTask;

class sqlWriteTask extends AsyncTask{
	private \SQLite3 $SQLite3;
	private array $shop;
	private string $pos;

	public function __construct(\SQLite3 $SQLite3, string $pos , array $shop){
		$this->SQLite3 = $SQLite3;
		$this->pos = $pos;
		$this->shop = $shop;
	}

	public function onRun(): void{
		if ($this->shop['isEnable']){
			$enable = 1;
		}else{
			$enable = 0;
		}
		$sql = 'insert into SHOP(pos,item,damage,owner,currency,price,amount,enable) values ('.$this->pos.','.$this->shop['item'].','.$this->shop['meta'].','.$this->shop['Owner'].','.$this->shop['currency'].','.$this->shop['price'].','.$this->shop['amount'].','.$enable.')';
		$this->SQLite3->exec($sql);
	}

}