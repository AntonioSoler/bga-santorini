<?php

class Dionysus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = DIONYSUS;
    $this->name  = clienttranslate('Dionysus');
    $this->title = clienttranslate('God of Wine');
    $this->text  = [
      clienttranslate("Your Build: Each time a Worker you control creates a Complete Tower, you may take an additional turn using an opponent Worker instead of your own. No player can win during these additional turns.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */

}
