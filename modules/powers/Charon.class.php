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
      clienttranslate("Your Move: Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function stateStartOfTurn()
  {
    $arg = [];
    $this->argUsePower($arg);
    return (count($arg['workers']) > 0)? 'power' : 'move';
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
        if (!$this->game->board->isNeighbour($worker, $worker2, '')){
          continue;
        }

        // Check space behind is free
        $space = $this->game->board->getSpaceBehind($worker2, $worker, $accessibleSpaces);
        if (!is_null($space)){
          $worker['works'][] = $this->game->board->getCoords($worker2);
        }
      }
    }

    Utils::cleanWorkers($arg);
  }


  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];

    // Get info about workers and space
    $worker = $this->game->board->getPiece($wId);
    $worker2 = $this->game->board->getPieceAt($space);
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    $newSpace = $this->game->board->getSpaceBehind($worker2, $worker, $accessibleSpaces);

    // Ferry worker
    self::DbQuery("UPDATE piece SET x = {$newSpace['x']}, y = {$newSpace['y']}, z = {$newSpace['z']} WHERE id = {$worker2['id']}");
    $this->game->log->addForce($worker2, $newSpace);

    // Notify (same text as Minotaur to help translators)
    $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name}'), [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker2,
      'space' => $newSpace,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($newSpace['z'])],
    ]);
  }


  public function argPlayerMove(&$arg){
    $action = $this->game->log->getLastAction('usedPower');
    // No power used before => usual rule
    if ($action == null) {
      return;
    }

    Utils::filterWorkersById($arg, $action[0]);
  }
}
