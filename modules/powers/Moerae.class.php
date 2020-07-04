<?php

class Moerae extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MOERAE;
    $this->name  = clienttranslate('Moerae');
    $this->title = clienttranslate('Goddesses of Fate');
    $this->text  = [
      clienttranslate("[Setup:] Take the Map, Shield, and Fate Token. Behind your Shield, secretly select a 2 X 2 square of Fate spaces by placing your Fate Token on the Map. When placing your Workers, place 3 of your color. "),
      clienttranslate("[Win Condition:] If an opponent Worker attempts to win by moving into one of your Fate spaces, you win instead."),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = -1;
  }

  /* * */
}
