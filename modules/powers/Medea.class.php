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
      clienttranslate("End of Your Turn: Once, remove one block from under any number of Workers neighboring your unmoved Worker. You also remove any Tokens on the blocks.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */
}
