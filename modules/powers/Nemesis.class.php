<?php

class Nemesis extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = NEMESIS;
    $this->name  = clienttranslate('Nemesis');
    $this->title = clienttranslate('Goddess of Retribution');
    $this->text  = [
      clienttranslate("End of Your Turn: If none of an opponent's Workers neighbor yours, you may force both of your Workers to spaces occupied by two of an opponent's Workers, and vice versa."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
  }

  /* * */
}
