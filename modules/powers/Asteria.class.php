<?php

class Asteria extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ASTERIA;
    $this->name  = clienttranslate('Asteria');
    $this->title = clienttranslate('Goddess of Falling Stars');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If one of your Workers moved down this turn, you may build a dome on any unoccupied space at any level.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 45;
  }

  /* * */
}
