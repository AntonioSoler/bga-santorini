<?php

class Hydra extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HYDRA;
    $this->name  = clienttranslate('Hydra');
    $this->title = clienttranslate('Many-Headed Monster');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If none of your Workers neighbor each other, gain a new Worker and place it in one of the lowest unoccupied spaces next to the Worker you moved. Otherwise, remove one of your Workers from play."),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 43;

    $this->implemented = true;
  }

  /* * */

  public function stateAfterBuild()
  {
    return 'power';
  }

  public function isNeighbouring($worker, $myWorkers)
  {
    foreach ($myWorkers as $worker2) {
      if ($this->game->board->isNeighbour($worker, $worker2)) {
        return true;
      }
    }
    return false;
  }

  public function isIndependentSet()
  {
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach ($myWorkers as $worker) {
      if ($this->isNeighbouring($worker, $myWorkers)) {
        return false;
      }
    }
    return true;
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;

    if ($this->isIndependentSet()) {
      // Obtain a new worker
      $arg['type'] = 'add';
      $this->game->log->addAction('HydraPower', [], ['type' => 'add']);
      $move = $this->game->log->getLastMove();
      $workers = $this->game->board->getPlacedWorkers($this->playerId);
      Utils::filterWorkersById($workers, $move['pieceId']);
      $worker = $workers[0];

      $spaces = $this->game->board->getNeighbouringSpaces($worker, "build");
      $minHeight = array_reduce($spaces, function ($carry, $space) {
        return min($carry, $space['z']);
      }, 4);
      Utils::filter($spaces, function ($space) use ($minHeight) {
        return $space['z'] == $minHeight;
      });
      $worker['works'] = $spaces;
      $arg['workers'] = [$worker];
    } else {
      // Discard a worker
      $arg['type'] = 'remove';
      $this->game->log->addAction('HydraPower', [], ['type' => 'remove']);
      $empty = [
        'id' => 0,
        'playerId' => $this->playerId,
        'works' => [],
      ];
      $workers = $this->game->board->getPlacedWorkers($this->playerId);
      foreach ($workers as $worker) {
        $coords =  SantoriniBoard::getCoords($worker, 0, true);
        // Save the worker ID in the 'arg' field
        $coords['arg'] = [$worker['id']];
        $empty['works'][] = $coords;
      }
      $arg['workers'] = [$empty];
    }
  }

  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];

    $action = $this->game->log->getLastAction("HydraPower");
    if ($action['type'] == "add") {
      $id = $this->getPlayer()->addWorker('m', 'hand');
      $extraWorker = $this->game->board->getPiece($id);
      $this->placeWorker($extraWorker, $space);
    } else {
      $worker = $this->game->board->getPiece($space['arg']);
      $this->removePiece($worker);
    }
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }
}
