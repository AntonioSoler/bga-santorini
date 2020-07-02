<?php

class Gaea extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = GAEA;
    $this->name  = clienttranslate('Gaea');
    $this->title = clienttranslate('Goddess of the Earth');
    $this->text  = [
      clienttranslate("[Setup:] Place 2 extra Workers of your color on your God Power card."),
      clienttranslate("[Any Build:] When a Worker builds a dome, Gaea may immediately place a Worker from her God Power card onto a ground-level space neighboring the dome.")
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 32;
  }

  /* * */
}
