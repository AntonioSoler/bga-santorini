<?php

class Eris extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ERIS;
    $this->name  = clienttranslate('Eris');
    $this->title = clienttranslate('Goddess of Discord');
    $this->text  = [
      clienttranslate("Alternative Turn: No player can win or lose this turn. Move and build with an opponent Worker that was not the one your opponent most recently moved.")
    ];
    $this->playerCount = [2, 4];
    $this->golden  = true;
  }

  /* * */
}
