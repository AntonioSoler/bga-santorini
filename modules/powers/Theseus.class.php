<?php

class Theseus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = THESEUS;
    $this->name  = clienttranslate('Theseus');
    $this->title = clienttranslate('Slayer of the Minotaur');
    $this->text  = [
      clienttranslate("End of Your Turn: Once, if any of your Workers is exactly 2 levels below any neighboring opponent Workers, remove one of those opponent Workers from play.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */
}
