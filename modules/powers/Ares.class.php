<?php

class Ares extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = ARES;
    $this->name  = clienttranslate('Ares');
    $this->title = clienttranslate('God of War');
    $this->text  = [
      clienttranslate("End of Your Turn: You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = false;
  }

  /* * */

}
