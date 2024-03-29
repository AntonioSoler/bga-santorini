<?php

class Odysseus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ODYSSEUS;
    $this->name  = clienttranslate('Odysseus');
    $this->title = clienttranslate('Cunning Leader');
    $this->text  = [
      clienttranslate("[Start of Your Turn:] [Once], force to unoccupied corner spaces any number of opponent Workers that neighbor your Workers."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 29;

    $this->implemented = true;
  }

  /* * */

  public function stateStartOfTurn()
  {
    $arg = [];
    $this->argUsePower($arg);
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : null;
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['ifPossiblePower'] = ODYSSEUS;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $arg['workers'] = $this->game->board->getPlacedOpponentWorkers();
    $corners = array_values(array_filter($this->game->board->getAccessibleSpaces(), function ($space) {
      return $this->game->board->isCorner($space);
    }));
    if (!empty($corners)) {
      $myWorkers = $this->game->board->getPlacedActiveWorkers();
      foreach ($arg['workers'] as &$worker) {
        foreach ($myWorkers as $myWorker) {
          if ($this->game->board->isNeighbour($worker, $myWorker)) {
            $worker['works'] = $corners;
          }
        }
      }
    }
    
    
    $forces = $this->game->log->getLastActions(['force']);
    
    // remove previously forced workers
    $prevForceIds = array_map(function ($force) {
      return $force['piece_id'];
    }, $forces);
    Utils::filterWorkersById($arg, $prevForceIds, false);
    
  }
  
  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = ODYSSEUS;
  }

  public function usePower($action)
  {
    $worker = $this->game->board->getPiece($action[0]);
    $space = $action[1];

    // Force the worker to the corner
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker,
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($worker, $space),
    ]);
  }

  public function stateAfterUsePower()
  {
    $arg = [];
    $this->argUsePower($arg);
    return (count($arg['workers']) > 0) ? 'power' : 'move';
  }

  public function stateAfterSkipPower()
  {
    return 'move';
  }
}
