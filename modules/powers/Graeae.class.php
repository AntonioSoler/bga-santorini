<?php

class Graeae extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = GRAEAE;
    $this->name  = clienttranslate('Graeae');
    $this->title = clienttranslate('The Gray Hags');
    $this->text  = [
      clienttranslate("Setup: When placing your Workers, place 3 of your color."),
      clienttranslate("Your Build: Build with either Worker that did not move."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 26;
  }

  /* * */
}
