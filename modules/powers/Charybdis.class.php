<?php

class Charybdis extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = CHARYBDIS;
    $this->name  = clienttranslate('Charybdis');
    $this->title = clienttranslate('Whirlpool Monster');
    $this->text  = [
      clienttranslate("Setup: Place 2 Whirlpool Tokens on your God Power card."),
      clienttranslate("End of Your Turn: You may place a Whirlpool Token from your God Power card on any unoccupied space on the board."),
      clienttranslate("Any Time: When both Whirlpool Tokens are in unoccupied spaces, a Worker that moves onto a space containing a Whirlpool Token must immediately move to the other Whirlpool Token's space. This move is considered to be in the same direction as the previous move. When a Whirlpool Token is built on or removed from the board, it is returned to your God Power card.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = false;
  }

  /* * */

}
