<?php

class Siren extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SIREN;
    $this->name  = clienttranslate('Siren');
    $this->title = clienttranslate('Alluring Sea Nymph');
    $this->text  = [
      clienttranslate("[Setup:] Place the Arrow Token beside the board and orient it to indicate the direction of the Siren's Song."),
      clienttranslate("[Alternative Turn:] Force any one or more opponent Workers one space in the direction of the Siren's Song to unoccupied spaces at any level. Then build with any of your Workers."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 44;
    

    $this->implemented = true;
  }



// TODO: don't know why UI is not displayed
  public function getUiData()
  {
    $data = parent::getUiData();
    $data['counter'] = '--';
    
    if ($this->playerId == null) 
      return $data;
    
    $token = $this->getToken();
    if ($token['location'] == 'board')
      $data['counter'] = $this->game->board->getMsgCoords($token);
      
    return $data;
  }

  public function setup()
  {
    $this->getPlayer()->addToken('tokenArrow');
    $this->updateUI();
  }

  public function getToken()
  {
    return $this->game->board->getPiecesByType('tokenArrow')[0];
  }
  
  public function getTokenSpace()
  {
    return ['x' => 4, 'y' => 5, 'z' => 1];
  }


  public function argPlaceSetup(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;

    
    $center = ['x' => 2, 'y' => 2, 'z' => 0];
    $works = [];
   
    for ($x = 1; $x < 4; $x++) {
      for ($y = 1; $y < 4; $y++) {
        if ($x == $y && $x == 2)
          continue;
        $space = ['x' => $x + 2*($x-2), 'y' => $y + 2*($y-2), 'z' => 0,  ];
        $dummy = ['x' => $x, 'y' => $y ];
        $space['direction'] = $this->game->board->getDirection($center, $dummy, []);
        $works[] = $space;
      }
    }
    
    $empty = [
      'id' => 0,
      'playerId' => $this->playerId,
      'works' => $works
    ];
    $arg['workers'] = [$empty];
    
    
    
  }
  
  
  public function placeSetup($action)
  {
    $token = $this->getToken();    
    $space = $action[0];
    
    $token['type_arg'] = $space['direction'];
    $space = $this->getTokenSpace();
    $this->placeToken($token, $space);
    
    $this->updateUI();
  }
   
    
  public function argUsePower(&$arg)
  {
    $forces = $this->game->log->getLastActions(['force']);
    
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = count($forces) > 0; // cannot skip 1st use
    
    $token = $this->getToken();
    if ($token['location'] != 'board')
      return;
    
    // setup each opponent worker
    $arg['workers'] =  $this->game->board->getPlacedOpponentWorkers($this->playerId);
    foreach ($arg['workers'] as &$oppw)
      $oppw['works'] = $this->game->board->getNeighbouringSpaces($oppw);
    
    // remove previously forced workers
    $prevForceIds = array_map(function ($force) {
      return $force['piece_id'];
    }, $forces);
    Utils::fiLterWorkersById($arg, $prevForceIds, false);
    
    // only keep the correct direction    
    $dir = $token['direction'];
    Utils::filterWorks($arg,  function ($space, $worker) use ($dir) {
      return $space['direction'] == $dir ;
    });
    
  }

  public function usePower($action)
  {    
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];

    $worker = $this->game->board->getPiece($wId);

    // Force worker
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker,
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($worker, $space),
    ]);
  }


  public function stateAfterUsePower()
  {
    $arg = $this->game->argUsePower();
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : 'build';
  }
  
  
  public function stateAfterSkipPower()
  {
    return 'build';
  }
  
  
  public function argPlayerMove(&$arg)
  {
    $arg['skippable'] = ($this->stateAfterUsePower() == 'power');
    return false;
  }


  public function stateAfterSkip()
  {
    return 'power';
  }
  
  
  public function argPlayerBuild(&$arg)
  {
    // No power use -> normal rule
    if (count($this->game->log->getLastActions(['move'])) > 0) {
      return;
    }

    // Otherwise, let the player build with any worker
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
    }
  }  
  
  
}

