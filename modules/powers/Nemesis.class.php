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
      clienttranslate("End of Your Turn: If none of an opponent's Workers neighbor yours, you may force as many of your opponent's Workers as possible to take the spaces you occupy, and vice versa.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = false;
  }

  /* * */
}
