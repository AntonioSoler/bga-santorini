<?php

class Adonis extends SantoriniHeroPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = ADONIS;
    $this->name  = clienttranslate('Adonis');
    $this->title = clienttranslate('Devastatingly Handsome');
    $this->text  = [
      clienttranslate("End of Your Turn: Once, choose an opponent Worker. If possible, that Worker must be neighboring one of your Workers at the end of their next turn.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */

}
