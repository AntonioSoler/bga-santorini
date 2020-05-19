<?php

class Eros extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = EROS;
    $this->name  = clienttranslate('Eros');
    $this->title = clienttranslate('God of Desire');
    $this->text  = [
      clienttranslate("Setup: Place your Workers anywhere along opposite edges of the board."),
      clienttranslate("Win Condition: You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game).")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [];
    $this->golden  = false;
  }

  /* * */

}
  