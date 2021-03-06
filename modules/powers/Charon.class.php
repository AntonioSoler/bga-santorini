<?php

class Charon extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHARON;
    $this->name  = clienttranslate('Charon');
    $this->title = clienttranslate('Ferryman to the Underworld');
    $this->text  = [
      clienttranslate("[Your Move:] Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 11;

    $this->implemented = true;
  }

  /* * */
  public function stateStartOfTurn()
  {
    $arg = $this->game->argUsePower();
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      foreach ($oppWorkers as $worker2) {
        // Only possible if workers are neighbors
        if (!$this->game->board->isNeighbour($worker, $worker2, '')) {
          continue;
        }

        // Check space behind is free
        $space = $this->game->board->getSpaceBehind($worker2, $worker, $accessibleSpaces);
        if (!is_null($space)) {
          $worker['works'][] = SantoriniBoard::getCoords($worker2, 0, true);
        }
      }
    }
  }


  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];

    // Get info about workers and space
    $worker = $this->game->board->getPiece($wId);
    $worker2 = $this->game->board->getPiece($space['id']);
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    $newSpace = $this->game->board->getSpaceBehind($worker2, $worker, $accessibleSpaces);

    // Ferry worker
    $this->game->board->setPieceAt($worker2, $newSpace);
    $this->game->log->addForce($worker2, $newSpace);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker2,
      'space' => $newSpace,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($newSpace['z'])],
      'coords' => $this->game->board->getMsgCoords($worker2, $newSpace),
    ]);
  }

  public function stateAfterUsePower()
  {
    return 'move';
  }

  public function stateAfterSkipPower()
  {
    return 'move';
  }


  public function argPlayerMove(&$arg)
  {
    $action = $this->game->log->getLastAction('usedPower');
    // No power used before => usual rule
    if ($action == null) {
      return;
    }

    Utils::filterWorkersById($arg, $action[0]);
  }
}
