<?php

class Heracles extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HERACLES;
    $this->name  = clienttranslate('Heracles');
    $this->title = clienttranslate('Doer of Great Deeds');
    $this->text  = [
      clienttranslate("[Instead of Your Build:] [Once], both your Workers build any number of domes (even zero) at any level."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 15;

    $this->implemented = true;
  }

  /* * */

  private function didSpecialBuild()
  {
    $builds = $this->game->log->getLastBuilds();
    return count($builds) != 1 || $builds[0]['to']['arg'] != $builds[0]['to']['z'];
  }

  public function argPlayerBuild(&$arg)
  {
    $count = count($this->game->log->getLastBuilds());
    $move = null;
    if ($count == 0) {
      $move = $this->game->log->getLastMove();
    }
    $arg['skippable'] = true;
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
      Utils::updateWorkerArgsBuildDome($worker, $move != null && $worker['id'] == $move['pieceId']);
    }
  }

  public function stateAfterBuild()
  {
    return $this->didSpecialBuild() ? 'buildAgain' : null;
  }

  public function preEndPlayerTurn()
  {
    if ($this->didSpecialBuild()) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('heroPower', $stats);
    }
    parent::preEndPlayerTurn();
  }
}
