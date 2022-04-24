<?php

class Scylla extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SCYLLA;
    $this->name  = clienttranslate('Scylla');
    $this->title = clienttranslate('Menacing Sea Monster');
    $this->text  = [
      clienttranslate("[Your Move:] If your Worker moves from a space that neighbors an opponent's Worker, you may force their Worker into the space yours just vacated."),
    ];
    $this->playerCount = [2, 4];
    $this->golden  = true;
    $this->orderAid = 36;

    $this->implemented = true;
  }

  /* * */


  public function argPlayerMove(&$arg)
  {
    $arg['mayMoveAgain'] = SCYLLA;
    $arg['ifPossiblePower'] = SCYLLA;
  }

  public function afterPlayerMove($worker, $work)
  {
    // Must use getPlacedOpponentWorkers() so Scylla cannot target Clio's invisible workers
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $targets = [];
    $space = SantoriniBoard::getCoords($worker);
    foreach ($oppWorkers as &$oppWorker) {
      if ($this->game->board->isNeighbour($worker, $oppWorker)) {
        $oppWorker['works'] = [$space];
        array_push($targets, $oppWorker);
      }
    }

    if (!empty($targets)) {
      $this->game->log->addAction("ScyllaTargets", [], ['space' => $space, 'workers' => $targets]);
    }
  }

  public function stateAfterMove()
  {
    $action = $this->game->log->getLastAction("ScyllaTargets");
    if ($action != null) {
      // Verify the space is actually empty (e.g., Charybdis may force Scylla back)
      $acc = $this->game->board->getAccessibleSpaces('build');
      Utils::filter($acc, function ($space) use ($action) {
        return ($space['x'] == $action['space']['x'] && $space['y'] == $action['space']['y']);
      });
      if (count($acc) > 0) {
        return 'power';
      }
    }
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;
    $action = $this->game->log->getLastAction("ScyllaTargets");
    if ($action == NULL){
        $arg['workers'] = [];
    }
    else{
        $arg['workers'] = $action['workers'];
    }
    
    $opponents = $this->game->playerManager->getOpponentsIds();
    foreach ($opponents as $opp) {
      $opponent = $this->game->playerManager->getPlayer($opp);
      $powers = $opponent->getPowers();
      foreach ($powers as $power) {
        if ($power->getId() == APHRODITE && $power->endOpponentTurn(true)) {
          $arg['skippable'] = false;
        }
      }
    }
  }

  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $oppWorker = $this->game->board->getPiece($wId);

    // Force worker
    $this->game->board->setPieceAt($oppWorker, $space);
    $this->game->log->addForce($oppWorker, $space);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $oppWorker,
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($oppWorker['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($oppWorker, $space),
    ]);
  }


  public function stateAfterUseOrSkipPower()
  {
    $move = $this->game->log->getLastMove();
    
    $worker = $this->game->board->getPiece($move['pieceId']);
    $worker['x'] = $move['from']['x'];
    $worker['y'] = $move['from']['y'];
    $worker['z'] = $move['from']['z'];
    $work = $move['to'];
    
    $this->game->powerManager->applyPower(["afterTeammateMove", "afterOpponentMove"], [$worker, $work]);
  
    return 'build';
  }
  

  public function stateAfterUsePower()
  {
    return $this->stateAfterUseOrSkipPower();
  }

  public function stateAfterSkipPower()
  {
    return $this->stateAfterUseOrSkipPower();
  }
}
