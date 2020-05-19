<?php

class Medusa extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = MEDUSA;
    $this->name  = clienttranslate('Medusa');
    $this->title = clienttranslate('Petrifying Gorgon');
    $this->text  = [
      clienttranslate("End of Your Turn: If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [NEMESIS];
    $this->golden  = true;
  }

  /* * */

}
  