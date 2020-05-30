<?php

class Aeolus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = AEOLUS;
    $this->name  = clienttranslate('Aeolus');
    $this->title = clienttranslate('God of the Winds');
    $this->text  = [
      clienttranslate("Setup: Place the Wind Token on your God Power card."),
      clienttranslate("End of Your Turn: If the Wind Token is on your God Power card, you may place the Wind Token beside the board and orient it to indicate the direction of the Wind. Otherwise, you may return the Wind Token to your God Power card."),
      clienttranslate("Any Move: If the Wind Token is not on your God Power card, Workers cannot move directly into the Wind."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 56;
  }

  /* * */
}
