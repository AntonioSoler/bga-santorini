<?php

class Hippolyta extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HIPPOLYTA;
    $this->name  = clienttranslate('Hippolyta');
    $this->title = clienttranslate('Queen of the Amazons');
    $this->text  = [
      clienttranslate("[Any Time:] All Workers except your female Worker may only move diagonally."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 47;
  }

  /* * */
}
