<?php

class Chronus extends Power
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return CHRONUS;
  }

  public static function getName() {
    return clienttranslate('Chronus');
  }

  public static function getTitle() {
    return clienttranslate('God of Time');
  }

  public static function getText() {
    return [
      clienttranslate("Win Condition: You also win when there are at least five Complete Towers on the board.")
    ];
  }

  public static function getPlayers() {
    return [2];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false;
  }

  /* * */
  protected function countTower(){
    $towers = self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND z = '3' AND type = 'lvl3'");
    return count($towers);
  }


  protected function checkWinning(&$arg){
    if($arg['win'])
      return;

    if ($this->countTower() < 5)
      return;

    $arg = [
      'win' => true,
      'msg' => clienttranslate('Five towers have been completed.'),
      'pId' => $this->playerId,
    ];
  }

  public function checkPlayerWinning(&$arg) {
    $this->checkWinning($arg);
   }

  public function checkOpponentWinning(&$arg) {
    $this->checkWinning($arg);
   }

}
