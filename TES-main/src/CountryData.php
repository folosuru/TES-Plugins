<?php


namespace folosuru\TES;


class CountryData{
	private $CountryData;
	private $plugin;
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function existCountry(string $name): bool{
		return array_key_exists($name,$this->CountryData);
	}
	public function makeCountry(string $name){
		$this->CountryData[$name] =array();
	}

}