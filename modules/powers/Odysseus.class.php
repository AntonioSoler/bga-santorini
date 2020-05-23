<?php

class Odysseus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ODYSSEUS;
    $this->name  = clienttranslate('Odysseus');
    $this->title = clienttranslate('Cunning Leader');
    $this->text  = [
      clienttranslate("Start of Your Turn: Once, force to unoccupied corner spaces any number of opponent Workers that neighbor your Workers.")
    ];
    $this->playerCount = [2];
    $this->golden  = false;
  }

  /* * */
}
