<?php

class Siren extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SIREN;
    $this->name  = clienttranslate('Siren');
    $this->title = clienttranslate('Alluring Sea Nymph');
    $this->text  = [
      clienttranslate("[Setup:] Place the Arrow Token beside the board and orient it to indicate the direction of the Siren's Song."),
      clienttranslate("[Alternative Turn:] Force any number of opponent Workers one space in the direction of the Siren's Song to unoccupied spaces at any level. Then build with any of your Workers."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 44;
  }

  /* * */
}
