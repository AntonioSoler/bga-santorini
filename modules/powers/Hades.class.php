<?php

class Hades extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HADES;
    $this->name  = clienttranslate('Hades');
    $this->title = clienttranslate('God of the Underworld');
    $this->text  = [
      clienttranslate("Opponent's Turn: Opponent Workers cannot move down.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function argOpponentMove(&$arg)
  {
    Utils::filterWorks($arg, function ($space, $worker) {
      return $space['z'] >= $worker['z'];
    });
  }
}
