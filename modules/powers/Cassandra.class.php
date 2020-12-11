<?php

class Cassandra extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CASSANDRA;
    $this->name  = clienttranslate('Cassandra');
    $this->title = clienttranslate('Disbelieved Seer');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], choose a Worker. It may not move until the start of your next turn."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = -1;

    $this->implemented = true;
  }

  /* * */

  public function stateAfterBuild()
  {
    return 'power';
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $empty = [
      'id' => 0,
      'playerId' => $this->playerId,
      'works' => [],
    ];
    $workers = $this->game->board->getPlacedWorkers();
    foreach ($workers as $worker) {
      $coords =  SantoriniBoard::getCoords($worker, 0, true);
      // Save the worker ID in the 'arg' field
      $coords['arg'] = [$worker['id']];
      $empty['works'][] = $coords;
    }
    $arg['workers'] = [$empty];
  }

  public function usePower($action)
  {
    $space = $action[1];
    $worker = $this->game->board->getPiece($space['arg']);
    $this->game->log->addAction('blockedWorker', [], ['wId' => $worker['id']]);

    // Notify
    $player = $this->game->playerManager->getPlayer($worker['player_id']);
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} (${coords}) cannot move this turn'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $player->getName(),
      'coords' => $this->game->board->getMsgCoords($worker),
    ]);
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  // Cassandra discard must happen after opponent's turn
  public function preEndPlayerTurn()
  {
  }

  public function preEndOpponentTurn()
  {
    $action = $this->game->log->getLastAction('blockedWorker', $this->playerId);
    if ($action != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }

  public function argOpponentMove(&$arg)
  {
    $action = $this->game->log->getLastAction('blockedWorker', $this->playerId);
    if ($action != null) {
      Utils::filterWorkersById($arg, $action['wId'], false);
    }
  }
}
