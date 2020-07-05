<?php

class Iris extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = IRIS;
    $this->name  = clienttranslate('Iris');
    $this->title = clienttranslate('Goddess of the Rainbow');
    $this->text  = [
      clienttranslate("[Your Move:] If there is a Worker neighboring your Worker and the space directly on the other side of it is unoccupied, your Worker may move to that space regardless of its level."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 37;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $workers = $this->game->board->getPlacedActiveWorkers();
    foreach ($workers as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        // Only possible if workers are neighbors
        if (!$this->game->board->isNeighbour($worker, $worker2)) {
          continue;
        }

        // Check if space behind opponent is free
        $space = $this->game->board->getSpaceBehind($worker, $worker2, $accessibleSpaces);
        if (!is_null($space)) {
          $worker['works'][] = $space;
        }
      }
    }
    Utils::mergeWorkers($arg, $workers);
  }

  public function afterPlayerMove($worker, $work)
  {
    $move = $this->game->log->getLastMove($this->playerId);
    if (!$this->game->board->isNeighbour($move['from'], $move['to'])) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
