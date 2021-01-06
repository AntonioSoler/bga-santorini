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
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId, true);

    foreach ($workers as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        if ($this->game->board->isNeighbour($worker, $worker2, 'move')) {
          Utils::addWork($worker, $worker2);

          // remove work to $worker2 space if exists (vs Hecate)
          Utils::filterWorks($arg, function ($space, $piece) use ($worker2) {
            return !($space['x'] == $worker2['x'] && $space['y'] == $worker2['y']);
          });
        }
      }
    }

    Utils::mergeWorkers($arg, $workers);
  }

  public function playerMove($worker, $work)
  {
    // If space is occupied, first do a force
    $worker2 = $this->game->board->getPiece($work);
    if ($worker2 != null && ($worker2['location'] == 'board' || $worker2['location'] == 'secret')) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->board->setPieceAt($worker2, $worker, $worker2['location']);
      $this->game->log->addForce($worker2, $worker, $stats);

      // Notify force
      $args = [
        'duration' => INSTANT,
        'i18n' => ['power_name', 'level_name'],
        'piece' => $worker2,
        'space' => $worker,
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
        'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
        'level_name' => $this->game->levelNames[intval($worker['z'])],
        'coords' => $this->game->board->getMsgCoords($worker2, $worker),
      ];

      $this->game->notifyWithSecret($worker2, $this->game->msg['powerForce'], $args, 'workerMoved');
    }

    // Always do a classic move
    return false;
  }
}
