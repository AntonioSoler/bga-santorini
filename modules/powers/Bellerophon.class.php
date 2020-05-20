<?php

class Bellerophon extends SantoriniHeroPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = BELLEROPHON;
    $this->name  = clienttranslate('Bellerophon');
    $this->title = clienttranslate('Tamer of Pegasus');
    $this->text  = [
      clienttranslate("Your Move: Once, your Worker moves up two levels.")
    ];
    $this->players = [2];
    
    $this->golden  = false;
  }

  /* * */

}
  