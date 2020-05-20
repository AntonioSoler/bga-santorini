<?php

class Hecate extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = HECATE;
    $this->name  = clienttranslate('Hecate');
    $this->title = clienttranslate('Goddess of Magic');
    $this->text  = [
      clienttranslate("Setup: Take the Map, Shield, and 2 Worker Tokens. Hide the Map behind the Shield and secretly place your Worker Tokens on the Map to represent the location of your Workers on the game board. Place your Workers last."),
      clienttranslate("Your Turn: Move a Worker Token on the Map as if it were on the game board. Build on the game board, as normal."),
      clienttranslate("Any Time: If an opponent attempts an action that would not be legal due to the presence of your secret Workers, their action is cancelled and they lose the rest of their turn. When possible, use their power on their behalf to make their turns legal without informing them.")
    ];
    $this->players = [2, 3];
    $this->golden  = false;
  }

  /* * */

}
