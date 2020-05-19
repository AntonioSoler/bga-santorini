<?php

class Aeolus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = AEOLUS;
    $this->name  = clienttranslate('Aeolus');
    $this->title = clienttranslate('God of the Winds');
    $this->text  = [
      clienttranslate("Setup: Place the Wind Token beside the board and orient it in any of the 8 directions to indicate which direction the Wind is blowing."),
      clienttranslate("End of Your Turn: Orient the Wind Token to any of the the eight directions."),
      clienttranslate("Any Move: Workers cannot move directly into the Wind.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [];
    $this->golden  = true;
  }

  /* * */

}
  