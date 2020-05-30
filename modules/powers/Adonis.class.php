<?php

class Adonis extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ADONIS;
    $this->name  = clienttranslate('Adonis');
    $this->title = clienttranslate('Devastatingly Handsome');
    $this->text  = [
      clienttranslate("End of Your Turn: Once, choose one of your Workers and an opponent Worker. If possible, the Workers must be neighboring at the end of your opponent's next turn."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 25;
  }

  /* * */
}
