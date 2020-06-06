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
      clienttranslate("[Setup:] Place 2 Whirlpool Tokens on your God Power card."),
      clienttranslate("[End of Your Turn:] You may place a Whirlpool Token from your God Power card on any unoccupied space on the board."),
      clienttranslate("[Any Time:] If a Worker moves onto a Whirlpool and the other Whirlpool is on the board in an unoccupied space, it is forced to the other Whirlpool's space. In this case, the player cannot win by moving their Worker to the first Whirlpool's space but can win as if it had moved up to the second space. Whirlpool Tokens built on or removed are returned to your God Power card."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 6;
  }

  /* * */
}
