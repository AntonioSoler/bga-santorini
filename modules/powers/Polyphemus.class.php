<?php

class Polyphemus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = POLYPHEMUS;
    $this->name  = clienttranslate('Polyphemus');
    $this->title = clienttranslate('Gigantic Cyclops');
    $this->text  = [
      clienttranslate("End of Your Turn: Once, your Worker builds up to 2 domes at any level on any unoccupied spaces on the board.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */
}
