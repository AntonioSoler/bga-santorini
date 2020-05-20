<?php

class Hera extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = HERA;
    $this->name  = clienttranslate('Hera');
    $this->title = clienttranslate('Goddess of Marriage');
    $this->text  = [
      clienttranslate("Opponent's Turn: An opponent cannot win by moving into a perimeter space.")
    ];
    $this->players = [2, 3, 4];
    
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function checkOpponentWinning(&$arg){
    if(!$arg['win'])
      return;

    $work = $this->game->log->getLastWork();
    if($work['action'] != 'move')
      return;

    if($this->game->board->isPerimeter($work['to']))
      $arg['win'] = false;
  }
}
