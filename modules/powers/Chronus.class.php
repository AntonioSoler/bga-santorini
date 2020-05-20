<?php

class Chronus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = CHRONUS;
    $this->name  = clienttranslate('Chronus');
    $this->title = clienttranslate('God of Time');
    $this->text  = [
      clienttranslate("Win Condition: You also win when there are at least five Complete Towers on the board.")
    ];
    $this->players = [2];
    
    $this->golden  = false;

    $this->implemented = true;
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
