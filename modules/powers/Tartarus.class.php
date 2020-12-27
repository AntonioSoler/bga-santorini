<?php

class Tartarus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TARTARUS;
    $this->name  = clienttranslate('Tartarus');
    $this->title = clienttranslate('God of the Abyss');
    $this->text  = [
      clienttranslate("[Setup:] Take the Map, Shield, and one Abyss Token. Place your Workers first. After all players' Workers are placed, hide the Map behind the Shield and secretly place your Abyss Token on an unoccupied space. This space is the Abyss."),
      clienttranslate("[Lose Condition:] If any player's Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 0;
    

    $this->implemented = true;
  }
  
  
  public function setup()
  {
    $this->getPlayer()->addToken('tokenAbyss');
  }
  
  
  // the token will be in SantoriniBoard, but in the hand of the player to stay secret. When the abyss needs to be displayed, board->getPlacedPieces() adds it temporarily
  public function getToken()
  {
    return $this->game->board->getPiecesByType('tokenAbyss')[0];
  }
  
  public function argChooseFirstPlayer(&$arg)
  {
    $pId = $this->getId();
    Utils::filter($arg['powers'], function ($power) use ($pId) {
      return $power == $pId;
    });

    $this->game->notifyAllPlayers('message', $this->game->msg['firstPlayer'], [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
    ]);
  }
  
  // set the abyss on the first turn
  public function stateStartOfTurn()
  {
    $done = $this->game->log->getActions(['usePowerTartarus'], $this->playerId);
    return (count($done) == 0) ? 'power' : null;
  }
  
  public function stateAfterUsePower()
  {
    return 'move';
  }
  
  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;

    $worker = $this->game->board->getPlacedActiveWorkers()[0]; // not sure how we can prevent a worker to be selected
    $worker['works'] = $this->game->board->getAccessibleSpaces();
    $arg['workers'] = [$worker];
  }
  
  public function usePower($action)
  {
    $space = $action[1];    
    
    // save the abyss space
    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addAction('usePowerTartarus', $stats, [
      'space' => $space,
    ]);
    
    $abyssToken = $this->getToken();
    $abyssToken['x'] = $space['x'];
    $abyssToken['y'] = $space['y'];
    $abyssToken['z'] = $space['z'];
  
   $this->game->notifyPlayer($this->playerId, 'workerPlaced', clienttranslate('${power_name}: ${player_name} places the abyss in ${coords}'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($space),
      'piece' => $abyssToken,
      'piece_name' => $this->game->pieceNames[$abyssToken['type']],
    ]);
  }
  
  public function getAbyssSpace()
  {
    $myabyss = $this->game->log->getActions(['usePowerTartarus'], $this->playerId); // get the last abyss placed (possible in case of restart)
    
    if (count($myabyss) == 0)
      return null;
      
    $abyss = json_decode($myabyss[0]['action_arg'], true)['space'];
    
    
    return $abyss;
  }
  
  // move the Abyss display when built / remove on it
  public function updateAbyssHeight($space)
  {
    $abyssSpace = $this->getAbyssSpace();
    $abyssToken = $this->getToken();
    
    if ($this->game->board->isSameSpace($abyssSpace,$space))
    {
     $abyssSpace['z'] = count($this->game->board->getBlocksAt($abyssSpace));
      
     $this->game->notifyPlayer($this->playerId, 'workerMoved', '', [
      'piece' => $abyssToken,
      'piece_name' => $this->game->pieceNames[$abyssToken['type']],
      'space' => $abyssSpace,
      'level_name' => $this->game->levelNames[intval($abyssSpace['z'])],
      ]);
    }
    
  }
  
  
  public function afterOpponentBuild($worker, $work)
  {
   $this->updateAbyssHeight($work);
  }
  
  public function afterPlayerBuild($worker, $work)
  {
   $this->updateAbyssHeight($work);
  }
  
  // deal with Ares removing a bloc on the abyss
  public function preEndOpponentTurn()
  {
     $action = $this->game->log->getLastActions(['removal']);
     if (count($action) == 0)
      return;
     $piece = $this->game->board->getPiece($action[0]['piece_id']);
    
     $this->updateAbyssHeight($piece);
  }
  
  
  // principle: after a turn / possible win, check the first player to step on the abyss (this design allows players to restart)
  // this assumes that players do not move / force to a new space after a win, so does not work with the current implementation of Harpies
  // $loose parameter: throw announceLoose inside this function
  
  public function checkTurn($loose = true)
  {
    $logs = $this->game->log->logsForCancelTurn();

    $abyss = $this->getAbyssSpace(); 
    
    if (!$abyss)
      throw new BgaVisibleSystemException('Unexpected state in Tartarus: no abyss');
    
    $loserID = null;
    
    foreach (array_reverse($logs) as $log) {
      $args = json_decode($log['action_arg'], true);

      if ($log['action'] == 'move' or $log['action'] == 'force') {
        if ($this->game->board->isSameSpace($args['to'], $abyss))
        {
            $loserID = $this->game->board->getPiece($log['piece_id'])['player_id'];
            break;
        }
      } else if ($log['action'] == 'placeWorker') {
        if ($this->game->board->isSameSpace($args['to'], $abyss))
        {
            $loserID = $this->game->board->getPiece($log['piece_id'])['player_id'];
            break;
        }
      }

    }
    
    if ($loserID && $loose)
    {
      $loser = $this->game->playerManager->getPlayer($loserID);
      $this->game->announceLose(clienttranslate('${power_name}: ${player_name2} (${coords}) enters the Abyss.'), [
        'i18n' => ['power_name'],
        'power_name' => clienttranslate('Tartarus'),
        'player_name2' => $loser->getName(), // 2 because announce_lose rewrites player_name 
        'coords' => $this->game->board->getMsgCoords($abyss),
      ], $loserID);
    }
    
    return $loserID;
  }


  public function endPlayerTurn()
  {
    $this->checkTurn(); 
  }
  
  public function endOpponentTurn()
  {
    $this->checkTurn(); 
  }
  
 
  public function checkWinning(&$arg)
  {
    if (!$arg['win'])
      return;
          
    $loserID = $this->checkTurn(false);
  
  // this happens when a player wins after stepping on the abyss: instead of announcing the defeat directly, we update the message
  
    if ($loserID != null) {
      $abyss = $this->getAbyssSpace();
    
      $loser = $this->game->playerManager->getPlayer($loserID);
      $arg['win'] = true;
      $arg['pId'] = $this->game->playerManager->getOpponentsIds($loserID)[0];
      $arg['winStats'] = [[$this->playerId, 'usePower']];
      $msg = clienttranslate('${power_name}: ${player_name} (${coords}) enters the Abyss.');
      $this->game->notifyAllPlayers('message', $msg, [
        'i18n' => ['power_name'],
        'power_name' => clienttranslate('Tartarus'),
        'player_name' => $loser->getName(),
        'coords' => $this->game->board->getMsgCoords($abyss),
      ]);
    
      }
  }
  
  
  
  public function checkPlayerWinning(&$arg)
  {
    $this->checkWinning($arg);
  }
  
  public function checkOpponentWinning(&$arg)
  {
    $this->checkWinning($arg);
  }




  /* * */
}
