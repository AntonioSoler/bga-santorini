<?php

class Apollo extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = APOLLO;
    $this->name  = clienttranslate('Apollo');
    $this->title = clienttranslate('God Of Music');
    $this->text  = [
      clienttranslate("[Your Move:] Your Worker may move into an opponent Worker's space by forcing their Worker to the space yours just vacated."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 18;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);

    foreach ($workers as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        if ($this->game->board->isNeighbour($worker, $worker2, 'move')) {
          $worker['works'][] = ['x' => $worker2['x'], 'y' => $worker2['y'], 'z' => $worker2['z']];
        }
      }
    }

    Utils::mergeWorkers($arg, $workers);
  }

  public function playerMove($worker, $work)
  {
    // If space is free, we can do a classic move -> return false
    $worker2 = $this->game->board->getPieceAt($work);
    if ($worker2 == null) {
      return false;
    }

    // Switch workers
    $stats = [[$this->playerId, 'usePower']];
    $this->game->board->setPieceAt($worker, $worker2);
    $this->game->log->addMove($worker, $worker2);
    $this->game->board->setPieceAt($worker2, $worker);
    $this->game->log->addForce($worker2, $worker, $stats);

    // Notify
    $this->game->notifyAllPlayers('workerSwitched', clienttranslate('${power_name}: ${player_name} forces a swap with ${player_name2}'), [
      'i18n' => ['power_name'],
      'piece1' => $worker,
      'piece2' => $worker2,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
    ]);

    return true;
  }
}
