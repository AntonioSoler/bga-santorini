<?php

class Europa extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = EUROPA;
    $this->name  = clienttranslate('Europa & Talus');
    $this->title = clienttranslate('Queen & Guardian Automaton');
    $this->text  = [
      clienttranslate("Setup: Place the Talus Token on your God Power card."),
      clienttranslate("End of Your Turn: You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved."),
      clienttranslate("Any Time: All players treat the space containing the Talus Token as if it contains only a dome.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 9;
  }

  /* * */
}
