<?php

class Harpies extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HARPIES;
    $this->name  = clienttranslate('Harpies');
    $this->title = clienttranslate('Winged Menaces');
    $this->text  = [
      clienttranslate("Opponent's Turn: Each time an opponent's Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */
}
