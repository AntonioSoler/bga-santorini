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
      clienttranslate("Setup: Place the Arrow Token beside the board and orient it in any of the 8 directions to indicate the direction of the Siren's Song."),
      clienttranslate("Your Turn: You may choose not to take your normal turn. Instead, force one or more opponent Workers one space in the direction of the Siren's Song to unoccupied spaces at any level.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */
}
