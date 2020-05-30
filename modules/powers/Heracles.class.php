<?php

class Heracles extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HERACLES;
    $this->name  = clienttranslate('Heracles');
    $this->title = clienttranslate('Doer of Great Deeds');
    $this->text  = [
      clienttranslate("Instead of Your Build: Once, both your Workers build any number of domes (even zero) at any level."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 15;
  }

  /* * */
}
