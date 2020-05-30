<?php

class Charybdis extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHARYBDIS;
    $this->name  = clienttranslate('Charybdis');
    $this->title = clienttranslate('Whirlpool Monster');
    $this->text  = [
      clienttranslate("Setup: Place 2 Whirlpool Tokens on your God Power card."),
      clienttranslate("End of Your Turn: You may place a Whirlpool Token from your God Power card on any unoccupied space on the board."),
      clienttranslate("Any Time: Whirlpool Tokens built on or removed from the board are returned to your God Power card. A Worker cannot win by moving onto a whirlpool if the other whirlpool is on the board in an unoccupied space. Instead, the Worker is forced to the other whirlpool and may win as if it moved up to that space."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 6;
  }

  /* * */
}
