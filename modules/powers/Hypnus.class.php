<?php

class Hypnus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = HYPNUS;
    $this->name  = clienttranslate('Hypnus');
    $this->title = clienttranslate('God of Sleep');
    $this->text  = [
      clienttranslate("Start of Opponent's Turn: If one of your opponent's Workers is higher than all of their others, it cannot move.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [TERPSICHORE];
    $this->golden  = true;
  }

  /* * */

}
  