<?php

class Medea extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MEDEA;
    $this->name  = clienttranslate('Medea');
    $this->title = clienttranslate('Powerful Sorceress');
    $this->text  = [
      clienttranslate("[End of Your Turn:] Once, remove all blocks from under a Worker neighboring either of your Workers. You also remove any Tokens on the blocks."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 19;
  }

  /* * */
}
