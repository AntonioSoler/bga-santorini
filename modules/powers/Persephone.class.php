<?php

class Persephone extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = PERSEPHONE;
    $this->name  = clienttranslate('Persephone');
    $this->title = clienttranslate('Goddess of Spring Growth');
    $this->text  = [
      clienttranslate("Opponent's Turn: If possible, at least one Worker must move up this turn.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */

}
