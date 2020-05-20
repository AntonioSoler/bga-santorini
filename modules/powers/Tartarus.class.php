<?php

class Tartarus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = TARTARUS;
    $this->name  = clienttranslate('Tartarus');
    $this->title = clienttranslate('God of the Abyss');
    $this->text  = [
      clienttranslate("Setup: Take the Map, Shield, and one Abyss Token. Place your Workers first. After all players' Workers are placed, hide the Map behind the Shield and secretly place your Abyss Token on an unoccupied space. This space is the Abyss."),
      clienttranslate("Lose Condition: If any player's Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */

}
