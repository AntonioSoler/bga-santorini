<?php

class Minotaur extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MINOTAUR;
    $this->name  = clienttranslate('Minotaur');
    $this->title = clienttranslate('Bull-headed Monster');
    $this->text  = [
      clienttranslate("[Your Move:] Your Worker may move into an opponent Worker's space, if their Worker can be forced one space straight backwards to an unoccupied space at any level."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 59;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $workers = $this->game->board->getPlacedActiveWorkers();
    // Must use getPlacedOpponentWorkers() so Minotaur cannot target Clio's invisible workers
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers(null, true);
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');

    foreach ($workers as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        // Must be accessible
        if (!$this->game->board->isNeighbour($worker, $worker2, 'move')) {
          continue;
        }

        // Must be a free space behind
        $space = $this->game->board->getSpaceBehind($worker, $worker2, $accessibleSpaces);
        if (!is_null($space)) {
          Utils::addWork($worker, $worker2);
        }
      }
    }

    Utils::mergeWorkers($arg, $workers);
  }

  public function playerMove($worker, $work)
  {
    // If space is occupied, first do a force
    $worker2 = $this->game->board->getPiece($work); // TODO: does it work with Hecate? not sure if getPiece gets an ID
    if ($worker2 != null && ($worker2['location'] == 'board' || $worker2['location'] == 'secret')) {
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      $space = $this->game->board->getSpaceBehind($worker, $worker2, $accessibleSpaces);
      if (is_null($space)) {
        throw new BgaVisibleSystemException("Minotaur: No available space behind opponent worker");
      }
      $stats = [[$this->playerId, 'usePower']];
      $this->game->board->setPieceAt($worker2, $space, $worker2['location']);
      $this->game->log->addForce($worker2, $space, $stats);
      
      $args = [
        'i18n' => ['power_name', 'level_name'],
        'piece' => $worker2,
        'space' => $space,
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
        'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
        'level_name' => $this->game->levelNames[intval($space['z'])],
        'coords' => $this->game->board->getMsgCoords($worker2, $space),
      ];
      
      // Notify force
      $this->game->notifyWithSecret($worker2, $this->game->msg['powerForce'], $args, 'workerMovedInstant');
    }

    // Always do a classic move
    return false;
  }
}
