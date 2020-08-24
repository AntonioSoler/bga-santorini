<?php

class Aphrodite extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = APHRODITE;
    $this->name  = clienttranslate('Aphrodite');
    $this->title = clienttranslate('Goddess of Love');
    $this->text  = [
      clienttranslate("[Any Move:] If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers."),
    ];
    $this->playerCount = [2, 4];
    $this->golden  = false;
    $this->orderAid = 57;

    $this->implemented = true;
  }

  /* * */

  public function isNeighbouring($oppWorker)
  {
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach ($workers as $worker) {
      if ($this->game->board->isNeighbour($worker, $oppWorker, '')) {
        return true;
      }
      if (SantoriniBoard::isSameSpace($worker, $oppWorker)) {
        return true;
      }
    }

    return false;
  }

  public function startOpponentTurn()
  {
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);
    $forcedWorkers = [];
    foreach ($oppWorkers as $worker) {
      if ($this->isNeighbouring($worker)) {
        $forcedWorkers[] = $worker['id'];
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} (${coords}) must end this turn neighboring ${player_name2}'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'player_name' => $this->game->getActivePlayerName(), // opponent
          'player_name2' => $this->getPlayer()->getName(), // Aphrodite
          'coords' => $this->game->board->getMsgCoords($worker),
        ]);
      }
    }

    if (!empty($forcedWorkers)) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('forcedWorkers', $stats, ['workers' => $forcedWorkers]);
    }
  }

  public function getForcedWorkers()
  {
    $action = $this->game->log->getLastAction('forcedWorkers');
    if ($action == null) {
      return null;
    }
    return $action['workers'];
  }

  public function canFinishHere($worker, $space, $forcedWorkers)
  {
    return !in_array($worker['id'], $forcedWorkers) || $this->isNeighbouring($space);
  }

  /*
   * canKeepMoving:
   *   called for non-neighboring spaces, returns true if this space allows the player to keep moving (look ahead 1)
   */
  public function canKeepMoving($worker, $space, $mayMoveAgain)
  {
    if ($mayMoveAgain === false) {
      return false;
    } else if ($mayMoveAgain == HERMES && $space['z'] != $worker['z']) {
      // Hermes must stay on the same level to move again
      return false;
    } else if ($mayMoveAgain == TRITON && !$this->game->board->isPerimeter($space)) {
      // Triton must stay on the perimiter to move again
      return false;
    }

    // Intermediate moves cannot win the game
    if ($space['z'] == 3) {
      if ($this->game->board->isPerimeter($space) && in_array(HERA, $this->game->powerManager->getOpponentPowerIds())) {
        // Hera: Can't win on the permiter, so perimiter level 3 is valid
        return true;
      }
      if ($this->game->log->isAdditionalTurn(DIONYSUS)) {
        // Dionysus: Can't win during additional turn, so any level 3 is valid
        return true;
      }
      // Other level 3 space would win, invalid
      return false;
    }

    // All other cases are valid intermediate moves
    return true;
  }

  public function argOpponentMove(&$arg)
  {
    $forcedWorkers = $this->getForcedWorkers();
    if ($forcedWorkers == null) {
      return;
    }

    // Allow skip only if condition is satisfied
    if ($arg['skippable']) {
      foreach ($arg['workers'] as $worker) {
        $arg['skippable'] = $arg['skippable'] && $this->canFinishHere($worker, $worker, $forcedWorkers);
      }
    }

    // Last move must be neighboring or intermediate move must be valid
    $mayMoveAgain = $arg['mayMoveAgain'];
    Utils::filterWorks($arg, function ($space, $worker) use ($forcedWorkers, $mayMoveAgain) {
      return $this->canFinishHere($worker, $space, $forcedWorkers) || $this->canKeepMoving($worker, $space, $mayMoveAgain);
    });
  }

  public function endOpponentTurn()
  {
    $forcedWorkers = $this->getForcedWorkers();
    if ($forcedWorkers == null) {
      return;
    }

    foreach ($this->getForcedWorkers() as $workerId) {
      $move = $this->game->log->getLastMoveOfWorker($workerId);
      if ($move != null && !$this->isNeighbouring($move['to'])) {
        $this->game->announceLose(clienttranslate('${power_name}: ${player_name} cannot move to a space neighboring ${player_name2} and is eliminated!'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'player_name2' => $this->getPlayer()->getName(),
        ]);
      }
    }
  }
}
