<?php

class Graeae extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = GRAEAE;
    $this->name  = clienttranslate('Graeae');
    $this->title = clienttranslate('The Gray Hags');
    $this->text  = [
      clienttranslate("Setup: When placing your Workers, place 3 of your color."),
      clienttranslate("Your Build: You choose which Worker of yours builds.")
    ];
    $this->players = [2, 3];
    $this->banned  = [NEMESIS];
    $this->golden  = false;
  }

  /* * */

}
  