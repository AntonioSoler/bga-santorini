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
      clienttranslate("[Any Move:] If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers.")
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

  public function canFinishHere($worker, $space)
  {
    $forcedWorkers = $this->getForcedWorkers();
    return $forcedWorkers == null || !in_array($worker['id'], $forcedWorkers) || $this->isNeighbouring($space);
  }

  public function argOpponentMove(&$arg)
  {
    if ($this->getForcedWorkers() == null) {
      return;
    }

    // Allow skip only if condition is satisfied
    if ($arg['skippable']) {
      foreach ($arg['workers'] as $worker) {
        $arg['skippable'] = $arg['skippable'] && $this->canFinishHere($worker, $worker);
      }
    }


    // Last move => must be neighboring
    if ($arg['mayMoveAgain'] === false) {
      Utils::filterWorks($arg, function ($space, $worker) {
        return $this->canFinishHere($worker, $space);
      });

      if (empty($arg['workers'])) {
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: Your last move must be to a space neighboring ${player_name2}'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'player_name2' => $this->getPlayer()->getName(),
        ]);
      }
    } else if ($arg['mayMoveAgain'] === 'perimeter') {
      // Last move if not on perimeter => must be neighboring
      Utils::filterWorks($arg, function ($space, $worker) {
        return $this->canFinishHere($worker, $space) || $this->game->board->isPerimeter($space);
      });
    }
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
