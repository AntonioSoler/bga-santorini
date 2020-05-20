<?php

class Heracles extends SantoriniHeroPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = HERACLES;
    $this->name  = clienttranslate('Heracles');
    $this->title = clienttranslate('Doer of Great Deeds');
    $this->text  = [
      clienttranslate("End of Your Turn: Once, both your Workers build any number of domes (even zero) at any level.")
    ];
    $this->players = [2];
    
    $this->golden  = false;
  }

  /* * */

}
  