<?php

class Athena extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = ATHENA;
    $this->name  = clienttranslate('Athena');
    $this->title = clienttranslate('Goddess of Wisdom');
    $this->text  = [
      clienttranslate("Opponent's Turn: If one of your Workers moved up on your last turn, opponent Workers cannot move up this turn.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [];
    $this->golden  = false;

    $this->implemented = true;
  }

  /* * */

  public function hasMovedUp()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function($movedUp, $move){
      return $movedUp || $move['to']['z'] > $move['from']['z'];
    }, false);
  }

  public function argOpponentMove(&$arg)
  {
    if(!$this->hasMovedUp())
      return;

    Utils::filterWorks($arg, function($space, $worker){
        return $space['z'] <= $worker['z'];
    });
  }

}
