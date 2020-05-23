<?php

class Achilles extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ACHILLES;
    $this->name  = clienttranslate('Achilles');
    $this->title = clienttranslate('Volatile Warrior');
    $this->text  = [
      clienttranslate("Your Turn: Once, your Worker builds both before and after moving.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */
}
