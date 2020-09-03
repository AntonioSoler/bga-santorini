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

    if(!empty($targets)){
      $this->game->log->addAction("ScyllaTargets", [], ['workers' => $targets]);
    }
  }

  public function stateAfterMove()
  {
    return is_null($this->game->log->getLastAction("ScyllaTargets"))? null : 'power';
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;
    $action = $this->game->log->getLastAction("ScyllaTargets");
    $arg['workers'] = $action['workers'];
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

    // Notify (same text as Minotaur to help translators)
    $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name} (${coords})'), [
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

  public function stateAfterUsePower()
  {
    return 'build';
  }

  public function stateAfterSkipPower()
  {
    return 'build';
  }
}
