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
      $powerData = ['type' => 'add', 'special' => 'none', 'minHeight' => $minHeight];
      
      
      // if vs Hecate & |spaces|<=2, add spaces 1 level higher, store minheight and spaces

      $hecate = false;
      foreach ($this->game->playerManager->getOpponents($this->playerId) as $opponent) {
        foreach ($opponent->getPowerIds() as $power) {
            if ($power == HECATE)
                $hecate = true;
        }
      }


      $spaces2 = [];      
      // add spaces above until at least 3 spaces are available or all neighbors are

      while ($hecate && count($spaces) < 3 && count($spaces) > count($spaces2))
      {
          $powerData['minSpaces'] = $spaces;
          
          $spaces2 = $this->game->board->getNeighbouringSpaces($worker, "build");
          $minHeight2 = array_reduce($spaces2, function ($carry, $space) use ($minHeight) {
            return $space['z'] <= $minHeight ? $carry : min($carry, $space['z']);
          }, 4);
          Utils::filter($spaces2, function ($space) use ($minHeight2) {
            return $space['z'] <= $minHeight2;
          });
          
          $worker['works'] = $spaces2;
          $arg['workers'] = [$worker];
          $powerData['special'] = HECATE;
          $minHeight = $minHeight2;
          $temp = $spaces;
          $spaces = $spaces2;
          $spaces2 = $temp;    
      }      
      
      $this->game->log->addAction('HydraPower', [], $powerData);
      
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
      
      if ($action['special'] != HECATE)
        return;
      if ($space['z'] > $action['minHeight'])
        $this->game->log->addAction('HydraBets', [], ['space' => $space]);
        
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
  
  public function endPlayerTurn()
  {
    $betOnHecate = $this->game->log->getLastAction("HydraBets");
    if ($betOnHecate == null)
      return;
  
    // test if Hecate endPlayerTurn is legal. if yes, test if should cancel power
    $powerActions = $this->game->log->getLastActions(["HydraPower"]);
    if (count($powerActions) == 0)
      return;
      
    $powerData = json_decode($powerActions[0]['action_arg'], true);
    
    if ($powerData['special'] != HECATE)
        return;
    
    $hecatePower = null;
    foreach ($this->game->playerManager->getOpponents($this->playerId) as $opponent) {
      foreach ($opponent->getPowers() as $power) {
          if ($power->getId() == HECATE)
              $hecatePower = $power;
      }
    }
    
    if ($hecatePower == null)
      throw new BgaVisibleSystemException("Hydra does not see Hecate as an opponent anymore");
      
    if ($hecatePower->endOpponentTurn(true))
      return; // there was a conflict before so Hecate will block the whole turn
      
    $spaces = $powerData['minSpaces'];
    
    $legal = true;
    foreach($spaces as $space){
      if ($space['z'] < $betOnHecate['space']['z'] && count($this->game->board->getPiecesAt($space, 'secret')) == 0) // no secret token are in game
        $legal = false;
    }
    
    if ($legal)
      return;
    
    // Cancel the turn from this move onward
    $this->game->cancelPreviousWorks($powerActions[0]['log_id']);
    $args = [
      'i18n' => ['power_name'],
      'power_name' => $hecatePower->getName(),
      'player_name' => $hecatePower->getPlayer()->getName(),
      'player_name2' => $this->getPlayer()->getName(),
      'power_name2' => $this->getName(),
      'coords1' => $this->game->board->getMsgCoords($betOnHecate['space']),
      'coords2' => $this->game->board->getMsgCoords($spaces[0]),
      'coords3' => count($spaces)>1 ? clienttranslate(', ') . $this->game->board->getMsgCoords($spaces[1]) : '',
    ];
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: the set of spaces {${coords2}${coords3}} is not fully occupied by ${player_name}\'s secret Workers so ${player_name2} cannot place a Worker in ${coords1} when using ${power_name2}\'s power.'), $args);
      
    
  }
  
}
