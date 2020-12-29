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
    $this->playerCount = [2]; // TODO cases to check for 3 players: put workers last, interactions with powers...
    $this->golden  = false;
    $this->orderAid = 64;
    
    $this->implemented = true;
  }
  
  
  public function argChooseFirstPlayer(&$arg)
  {
    // Hecate must not go first
    if (($key = array_search($this->id, $arg['powers'])) !== false) {
      unset($arg['powers'][$key]);
  }
  }

  
  public function getPlacedWorkers()
  {
    return $this->game->board->getPlacedWorkers($this->playerId, 'secret');
  }


  public function playerPlaceWorker($workerId, $x, $y, $z)
  {
    $worker = $this->game->board->getPiece($workerId);
    $team = $this->game->playerManager->getPlayer($this->playerId)->getTeam();
//    $typearg = $worker['type_arg'][0] . $team; // male or female
    $typearg = 'f' . $team; // two female workers so that we can easily reveal the location but not identify the worker
    $token = $this->getPlayer()->addToken('worker', $typearg, 'hand', VISIBLE_TO_PLAYER);
    
    $space = ['x' => $x, 'y' => $y, 'z' => $z];
    
    
    $this->placeToken($this->game->board->getPiece($token), $space);
    $this->game->board->setPieceAt($worker, $space, 'box');
    
    return true; // do not place another piece
  }
  
  public function argPlayerWork(&$arg, $action)
  {
    $myworkers = $this->getPlacedWorkers();
    foreach ($arg['workers'] as &$worker) {
      
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, $action);
      
 /*     Utils::filterWorks($arg, function($space, $piece) use ($myworkers) {
        return  !max(array_map(
                      function($s) use ($space) {
                      return ($space['x'] == $s['x'] && $space['y'] == $s['y']) ;
                      } , $myworkers )); } ); // remove Hecate worker spaces
    */}
  }
  
  public function argPlayerMove(&$arg)
  {
    $arg['workers'] = $this->getPlacedWorkers();
    $this->argPlayerWork($arg, 'move');
  }
  
  
  public function argPlayerBuild(&$arg)
  {
      //TODO: problem: cannot build
    $move = $this->game->log->getLastMove();
    if ($move == null)
      throw new BgaVisibleSystemException('Hecate build before move');
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
  
  
  
  // check if the turn was legal based on Hecate power, and cancel the last actions if necessary
  // TODO: treat Apollo / Minotaur
  // TODO: treat passive powers (Hypnus etc)
  public function endOpponentTurn()
  {
    $myWorkers = $this->getPlacedWorkers();
    
    $logs = $this->game->log->logsForCancelTurn();
    
    $logIdBreak = null;
    $space = null;
    
    foreach (array_reverse($logs) as $log) {
      if ($log['action'] == 'move' || $log['action'] == 'force' || $log['action'] == 'build'
       || $log['action'] == 'placeWorker' || $log['action'] == 'placeToken'
       || $log['action'] == 'moveToken') {
        $args = json_decode($log['action_arg'], true);
        $space = $args['to'];
        if (max(array_map(function($s) use ($space) {
                      return ($space['x'] == $s['x'] && $space['y'] == $s['y']) ;
                      } , $myWorkers ))) 
        {
          $logIdBreak = $log['log_id'];
          break;
        }
      }
    }
    
    if ($logIdBreak == null)
      return;
    
    
    
    // cancel end of turn and display it
    $moveIds = $this->game->log->cancelTurn($logIdBreak);
    
    
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
    
    $worker = Utils::search($myWorkers, function($s) use ($space) {return ($space['x'] == $s['x'] && $space['y'] == $s['y']);});
    
    if ($worker == null)
      throw new BgaVisibleSystemException('Unexpected state in Hecate: worker expected on a space');
    
    $this->game->board->setPieceAt($worker, $worker, 'board'); // display the worker temporarily
    
    $this->game->notifyAllPlayers('workerPlaced', '', [
      'piece' => $worker,
    ]);
    
    
    // explain what is happening
    $this->game->notifyAllPlayers('message', '${power_name}: due to a secret worker in ${coords}, an action of ${player_name} is illegal which cancels the rest of the turn', $args);
    
    // hide the secret worker again
    $this->game->board->setPieceAt($worker, $worker, 'secret');
    
    $this->game->notifyAllPlayers('pieceRemoved', '', [
      'piece' => $worker,
    ]);
    
  }





}
