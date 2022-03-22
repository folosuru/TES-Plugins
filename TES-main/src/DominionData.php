<?php


namespace folosuru\TES;


use pocketmine\player\Player;

class DominionData{
	private $DominionData;
	private $Plugin;

	public function __construct(Main $plugin){
		$this->Plugin =$plugin;
	}

	public function addDominion(int $x,int $y){
		$this->DominionData["dominion"][$x][$y] = array(

		);
	}

	public function convertArea(int $pos){
		return floor($pos/$this->Plugin->setting["AreaWidth"]);
	}
}