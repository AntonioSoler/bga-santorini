<?php

class Proteus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PROTEUS;
    $this->name  = clienttranslate('Proteus');
    $this->title = clienttranslate('Shapeshifting Sea God');
    $this->text  = [
      clienttranslate("Setup: When placing your Workers, place 3 of your color."),
      clienttranslate("Your Move: After your Worker moves, if possible, force one of your other Workers into the space just vacated."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
  }

  /* * */
}
