<?php

declare(strict_types=1);

namespace folosuru\TES;

class PlayerData{
	private $UserData;
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function setUserData($UserData): void {
		$this->UserData = $UserData;
	}


	public function getUserData() {
		return $this->UserData;
	}

	public function SaveUserData(){
		$arr = json_encode($this->UserData);
		file_put_contents("Player_data.json" , $arr);
	}

	public function getPlayerMoney(string $currency){
		$this->CheckExistCurrency($currency);
		return $this->UserData["money"][$currency];
	}

	public function setPlayerMoney(string $currency,int $amount){
		$this->UserData["money"][$currency] = $amount;
	}

	public function addPlayerMoney(string $currency,int $amount){
		$this->CheckExistCurrency($currency);
		$this->UserData["money"][$currency] += $amount;
	}

	public function getCountry(){
		return $this->UserData["country"];
	}

	public function removeMoney(string $currency,int $value) : void{
		$this->UserData["money"][$currency] -= $value;
	}


	private function CheckExistCurrency(string $currency){
		if (!array_key_exists($currency,$this->UserData["money"])){
			$this->UserData["money"][$currency] = 0;
		}
	}
}