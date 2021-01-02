<?php

class Hecate extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HECATE;
    $this->name  = clienttranslate('Hecate');
    $this->title = clienttranslate('Goddess of Magic');
    $this->text  = [
      clienttranslate("[Setup:] Take the Map, Shield, and 2 Worker Tokens. Hide the Map behind the Shield and secretly place your Worker Tokens on the Map to represent the location of your Workers on the game board. Place your Workers last."),
      clienttranslate("[Your Turn:] Move a Worker Token on the Map as if it were on the game board. Build on the game board, as normal."),
      clienttranslate("[Any Time:] If an opponent attempts an action that would not be legal due to the presence of your secret Workers, their action is cancelled and they lose the rest of their turn. When possible, use their power on their behalf to make their turns legal without informing them."),
    ];
    $this->playerCount = [2]; // TODO problematic cases for 3 players: put workers last, interactions with powers and restart implementation (Limus, Harpies)...
    $this->golden  = false;
    $this->orderAid = 64;
    
    $this->implemented = true;
  }
  
  
  public function getDummyWorker()
  {
    $logs = $this->game->log->getActions(['powerData'], $this->playerId);
    $dummy = count($logs) > 0 ? json_decode($logs[0]['action_arg'], true) : null;
    if ($dummy == null)
    {
      $dummy = $this->getPlayer()->addWorker('f', 'hand'); // dummy worker used only for display 
      $this->game->log->addAction('powerData', [], $dummy, $this->playerId);
    }
    return $this->game->board->getPiece($dummy);
  }
  
  
  public function argChooseFirstPlayer(&$arg)
  {
  
    $pId = $this->getId();
    Utils::filter($arg['powers'], function ($power) use ($pId) {
      return $power != $pId;
    });
  }

  
  public function getPlacedWorkers()
  {
    return $this->game->board->getPlacedWorkers($this->playerId, true);
  }


  public function playerPlaceWorker($workerId, $x, $y, $z)
  {
    $worker = $this->game->board->getPiece($workerId);
    
    $space = ['x' => $x, 'y' => $y, 'z' => $z,  'arg' => null];
    
    
    $this->game->board->setPieceAt($worker, $space, 'secret');
    
    $worker = $this->game->board->getPiece($workerId); // update space
    // Notify
    $args = [
      'i18n' => ['power_name', 'piece_name'],
      'piece' => $worker,
      'piece_name' => $this->game->pieceNames[$worker['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($space),
    ];

    $this->game->notifyPlayer($this->getPlayerId(), 'workerPlaced', $this->game->msg['powerPlacePiece'], $args);
    unset($args['piece']);
    $args['i18n'][] = 'coords';
    $args['coords'] = $this->game->specialNames['secret'];
    $this->game->notifyAllPlayers('message', $this->game->msg['powerPlacePiece'], $args);
    return true; // do not place another piece
  }
  
  public function argPlayerWork(&$arg, $action)
  {
    $myworkers = $this->getPlacedWorkers();
    foreach ($arg['workers'] as &$worker)
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, $action);
    
    Utils::filterWorks($arg, function($space, $piece) use ($myworkers) {
      return  !max(array_map(
                    function($s) use ($space) {
                    return ($space['x'] == $s['x'] && $space['y'] == $s['y']) ;
                    } , $myworkers )); } ); // remove Hecate worker spaces
  }
  
  public function argPlayerMove(&$arg)
  {
    $arg['workers'] = $this->getPlacedWorkers();
    $this->argPlayerWork($arg, 'move');
  }
  
  
  public function argPlayerBuild(&$arg)
  {
    $move = $this->game->log->getLastMove();
    if ($move == null)
      throw new BgaVisibleSystemException('Hecate build before move');
      
    $arg['workers'] = $this->getPlacedWorkers();
    Utils::filterWorkersById($arg, $move['pieceId']);
    $this->argPlayerWork($arg, 'build');
  }
  
  
  public function playerMove($worker, $space)
  {
    $this->game->board->setPieceAt($worker, $space, 'secret');
    $this->game->log->addMove($worker, $space);
    
    
    // Notify
    if ($space['z'] > $worker['z']) {
      $msg = $this->game->msg['moveUp'];
    } else if ($space['z'] < $worker['z']) {
      $msg = $this->game->msg['moveDown'];
    } else {
      $msg = $this->game->msg['moveOn'];
    }

    $args = [
      'i18n' => ['level_name'],
      'piece' => $worker,
      'space' => $space,
      'player_name' => $this->game->getActivePlayerName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($worker, $space)
    ];
    
    
    $this->game->notifyPlayer($this->playerId, 'workerMoved', $msg, $args);
    
    $args = [
      'i18n' => ['player_name'],
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->specialNames['secret']
    ];
    
    $this->game->notifyAllPlayers('message', '${player_name} moves to (${coords})', $args);
  
    
    return true; // do not move again
  }
  
  
// return null if the logged action is legal wrt the secret workers. Otherwise, return the problematic space
  public function getAffectedSpaceFromLog($log, $myWorkers)
  {    
      if (count($myWorkers) == 0)
        return null;
      
      Utils::filterWorkersById($myWorkers, $log['piece_id'], false);
  
      if ($log['action'] == 'move' || $log['action'] == 'force' || $log['action'] == 'build'
       || $log['action'] == 'placeWorker' || $log['action'] == 'placeToken'
       || $log['action'] == 'moveToken')
      { 
        $args = json_decode($log['action_arg'], true);
        $space = $args['to'];
      }
      elseif ($log['action'] == 'removal')
      {
        $space = $this->game->board->getPiece($log['piece_id']);
      }
      else
        return null;
       
      if (max(array_map(function($s) use ($space) {
                      return ($space['x'] == $s['x'] && $space['y'] == $s['y']) ;
                      } , $myWorkers )))
         return $space;
      else
        return null;
  
  }
  
  
  // check if the turn was legal based on Hecate power, and cancel the last actions if necessary
  public function endOpponentTurn()
  {
    $myWorkers = $this->getPlacedWorkers();
    
    $logs = $this->game->log->logsForCancelTurn();
    
    $space = null;
    
    foreach (array_reverse($logs) as $log) {
        $space = $this->getAffectedSpaceFromLog($log, $myWorkers);
        if ($space == null)
          continue;
        // cancel end of turn
        $moveIds = $this->game->log->cancelTurn($log['log_id']);    
        break;
    }
    
    if ($space == null)
    {
      // treat Medusa: kill secret workers only after we know the turn is legal
      $powers = $this->game->playerManager->getPlayer($this->game->getActivePlayerId())->getPowers();
      foreach ($powers as $power)
      {
        if ($power->getId() != MEDUSA)
          continue;
        $argKill = ['workers' => []];
        $power->argPlayerBuild($argKill, true); // get killable secret workers
        $power->endPlayerTurn($argKill); // kill them
      }
      return;
    }
    
    // display current board state
    $playerIds = $this->game->playerManager->getPlayerIds();
    foreach ($playerIds as $playerId) {
      $this->game->notifyPlayer($playerId, 'cancel', '' , [
        'placedPieces' => $this->game->board->getPlacedPieces($playerId),
          'moveIds' => $moveIds,
        ]);
     }
    
    // display the problematic secret worker
    $args = [
      'i18n' => ['player_name'],
      'player_name' => $this->game->getActivePlayerName(),
      'power_name' => $this->getName(),
      'coords' => $this->game->board->getMsgCoords($space)
    ];
    
    $dummy = $this->getDummyWorker(); // use a dummy one to avoid duplicates
    $dummy['x'] = $space['x'];
    $dummy['y'] = $space['y'];
    $dummy['z'] = $this->game->board->countBlocksAt($space);
    $this->game->notifyAllPlayers('revealPiece', '', ['piece' => $dummy]);
    
    // explain what is happening
    $this->game->notifyAllPlayers('message', '${power_name}: due to a secret worker in ${coords}, an action of ${player_name} is illegal which cancels the rest of the turn', $args);
    
    // hide the secret worker again
    $this->game->notifyAllPlayers('hidePiece', '', ['piece' => $dummy]);
    
  }




}
