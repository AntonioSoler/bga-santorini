<?php

class Charon extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHARON;
    $this->name  = clienttranslate('Charon');
    $this->title = clienttranslate('Ferryman to the Underworld');
    $this->text  = [
      clienttranslate("Your Move: Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */
}
