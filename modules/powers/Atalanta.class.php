<?php

class Atalanta extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ATALANTA;
    $this->name  = clienttranslate('Atalanta');
    $this->title = clienttranslate('Swift Huntress');
    $this->text  = [
      clienttranslate("Your Move: Once, your Worker moves any number of additional times.")
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 28;
  }

  /* * */
}
