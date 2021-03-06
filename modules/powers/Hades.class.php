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
      clienttranslate("[Opponent's Turn:] Opponent Workers cannot move down."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 3;

    $this->implemented = true;
  }

  /* * */

  public function argOpponentMove(&$arg)
  {
    // Useful against Dionysus
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorksUnlessMine($arg, $myWorkers, function ($space, $worker) {
      return $space['z'] >= $worker['z'];
    });
  }

  public function endOpponentTurn()
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addAction('stats', $stats);
  }
}
