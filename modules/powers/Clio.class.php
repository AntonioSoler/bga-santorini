<?php

class Clio extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CLIO;
    $this->name  = clienttranslate('Clio');
    $this->title = clienttranslate('Muse of History');
    $this->text  = [
      clienttranslate("Your Build: Place a Coin Token on each of the first 3 blocks your Workers build."),
      clienttranslate("Opponent's Turn: Opponents treat spaces containing your Coin Tokens as if they contain only a dome.")
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 39;
  }

  /* * */
}
