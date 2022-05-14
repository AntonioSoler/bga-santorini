<?php

class Persephone extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PERSEPHONE;
    $this->name  = clienttranslate('Persephone');
    $this->title = clienttranslate('Goddess of Spring Growth');
    $this->text  = [
      clienttranslate("[Opponent's Turn:] If possible, at least one Worker must move up this turn."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 4;

    $this->implemented = true;
  }


  /* * */
  
  public function canMoveUp($arg, $minlevel = 1, $maxlevel= 3)
  {
    $canMoveUp = false;
    foreach ($arg["workers"] as &$worker) {
      foreach ($worker['works'] as &$space) {
        if ($space['z'] >= $worker['z'] + $minlevel && $space['z'] <= $worker['z'] + $maxlevel) {
          $canMoveUp = true;
        }
      }
    }
    return $canMoveUp;
  }
  
  public function argOpponentBuild(&$arg)
  {    
    if ($arg['ifPossiblePower'] == PROMETHEUS){
      $test = $this->game->argPlayerMove(true);
      $canMoveUp = $this->canMoveUp($test);
    
      if ($canMoveUp)
        $arg['workers'] = [];
    }
    
    
    if ($arg['ifPossiblePower'] == ACHILLES){
      $test = $this->game->argPlayerMove(true);
      $canMoveUp = $this->canMoveUp($test);
      $canMoveSame  = $this->canMoveUp($test, 0, 0);
    
      if ($canMoveUp)
        $this->game->log->addAction('CanMoveUp', [] , ['move' => 'up']);
      if ($canMoveSame)
        $this->game->log->addAction('CanMoveUpHero', [] , ['move' => 'up']);
    }
    
  }
  
  public function argOpponentUsePower(&$arg)
  {
    $workers = $this->game->board->getPlacedActiveWorkers();
    $perseWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    
    if($arg['power'] == CHARON){
      // test whether he can move up without power
      $normalMoves = $this->game->argPlayerMove(true);
      Utils::filterWorks($normalMoves, function ($s,$w) use ($perseWorkers) { foreach ($perseWorkers as $work){ if ($this->game->board->isSameSpace($work,$s)) return false;} return true;});
      $mortalMoveUp = $this->canMoveUp($normalMoves);
      // test whether using the power allows a new move up
      $test = $arg;
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      Utils::filterWorks($test, function ($s,$w) use ($accessibleSpaces) {
                    if ($w['z'] != $s['z'] - 1)
                      return false; 
                    $newSpace = $this->game->board->getSpaceBehind($s, $w, $accessibleSpaces);
                    $pieces = $this->game->board->getPiecesAt($s);
                    $oppw = null;
                    foreach ($pieces as $piece){
                      if ($piece['type'] == 'worker')
                        $oppw = $piece;
                    }
                    if ($oppw == null)
                      throw new BgaVisibleSystemException('Persephone vs Charon: cannot get Persephone\'s worker');
                    $this->game->board->setPieceAt($oppw,$newSpace);
                    $AllMoves = $this->game->argPlayerMove(true);
                    $this->game->board->setPieceAt($oppw,$oppw);
      
                    foreach($AllMoves['workers'] as $worker){
                      foreach($worker['works'] as $work){
                        if ($this->game->board->isSameSpace($w,$worker) && $this->game->board->isSameSpace($s,$work))
                          return true;
                      }
                    }
                    return false;});
      
      if ($mortalMoveUp || !empty($test['workers']))
      {
        $this->game->log->addAction('CanMoveUp', [], ['move' => 'up']);
        if (!$mortalMoveUp)
        {
          $arg = $test;
          $arg['skippable'] = false;
        }
      }
    }
    
    
    if($arg['power'] == JASON){      
      $normalMoves = $this->game->argPlayerMove(true);
      $mortalMoveUp = $this->canMoveUp($normalMoves);
      
      $test = $arg;
      Utils::filterWorks($test, function ($s,$w)  { 
         $nb = $this->game->board->getNeighbouringSpaces($s);
         foreach ($nb as $sp){
          if ($sp['z'] == 1)
            return true;
         }
      });
      
      Utils::cleanWorkers($test);      
      if ($mortalMoveUp || !empty($test['workers']))
        $arg = $test; 
    }
    
    
    
    
    if ($arg['power'] == ODYSSEUS){
      
      $normalMoves = $this->game->argPlayerMove(true);
      $mortalMoveUp = $this->canMoveUp($normalMoves);
      
      if ($mortalMoveUp)
        $this->game->log->addAction('CanMoveUp', [], ['move' => 'up']);
      
      $powerMoveUp = false;
      foreach($workers as $odWorker){
        foreach($perseWorkers as $perWorker){
        if ($this->game->board->isNeighbour($odWorker,$perWorker) && $odWorker['z'] == $perWorker['z']-1)
          $powerMoveUp = true;
      }}
      
      if ($powerMoveUp && !$mortalMoveUp)
        $this->game->log->addAction('CanMoveUpHero', [] , ['move' => 'up']);       
    }
    
  }
  
  
  // TODO if vs Aphrodite, once moved up, has to check if can go to a space where $aphro->canFinishHere().
  // move the worker and use argteammatemove, argopponentmove for aphrodite, aeolus, hypnus etc
  // then apply charybdis or harpies effect
  // only works with Atalanta (2p game) for now
  public function testMultipleMoves(&$arg, $worker)
  {
    $visited = [];
    // possible params: 'init' = initial position; 'once' = one more move possible, 'stop' = no more move possible, 'conti' = can continue

    $queue = [[$worker, 'init']];    
        
    while (!empty($queue)){
      $elem = array_pop($queue);
      $space = $elem[0];
      $param = $elem[1];
      
      
      $reach = $this->game->board->getNeighbouringSpaces($space, 'move');
      if ($param == 'stop')
        $reach = [];
      
      
      foreach($reach as $sp){
        
        if ($sp['z'] > $space['z'])
          return true;
        if (!empty(array_filter($visited, function ($s) use ($sp) {return $this->game->board->isSameSpace($s   ,$sp);})) || 
            !empty(array_filter($queue  , function ($s) use ($sp) {return $this->game->board->isSameSpace($s[0],$sp);})))
          continue;
        $nextparam = 'stop';
        if ($param == 'init' && $arg['ifPossiblePower'] == ARTEMIS)
          $nextparam = 'once';
        if ($arg['ifPossiblePower'] == TRITON && $this->game->board->isPerimeter($sp))
          $nextparam = 'conti';
        if ($arg['ifPossiblePower'] == ATALANTA)
          $nextparam = 'conti';
        $queue[] = [$sp, $nextparam];
      }
      
      $visited[] = $space;
    }
    return false;  
  }
  
  
  public function argOpponentMove(&$arg)
  {
    
    $canMoveUp = $this->canMoveUp($arg);
    
    $charybdis = null;
    $harpies = null;
    $hypnus = null;
    foreach ($this->game->powerManager->getPowersInLocation('hand') as $power) {
      if ($power->getId() == CHARYBDIS)
        $charybdis = $power;
    }
    
    foreach ($this->game->playerManager->getOpponents() as $opponent) {
      foreach ($opponent->getPowers() as $power) {
      if ($power->getId() == HARPIES)
        $harpies = $power;
    }}

    $opponent = $this->game->playerManager->getPlayer();
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move', $opponent->getPowerIds());
    
    // if both workers can move, test if moving the top first can leave space for the second to move up
    $canMoveUpLater = false;
    
    if (in_array($arg['ifPossiblePower'] , [CASTOR, TERPSICHORE]) && $this->game->log->getLastMove() == null){
      if ($canMoveUp)
        $arg['skippable'] = false;
    
      $workers = $this->game->board->getPlacedActiveWorkers();
      if (count($workers) != 2)
        return;
      if (abs($workers[0]['z'] - $workers[1]['z']) != 1)
        return;
      $top = $workers[0]['z'] > $workers[1]['z'] ? $workers[0] : $workers[1];
      $bot = $workers[0]['z'] > $workers[1]['z'] ? $workers[1] : $workers[0];
      
      if (!$this->game->board->isNeighbour($top, $bot))
        return;
        
      $test = $arg;
      Utils::filterWorkersById($test, $top['id']);
      $botmovetop = false;
      if (empty($test['workers']))
        return;
      // test if there is a work that does not come back to the same space, and save its height
      $moveback = ($charybdis != null);
      $dummy = $bot;
      $dummy['z'] = $top['z'];
      $this->game->board->setPieceAt($top,$dummy);
      foreach ($test['workers'][0]['works'] as $work){
        $space = $work;
        if ($charybdis){
          $wp = $charybdis->whirlpooledSpace($space,$top);
          $space = count($wp) == 0 ? $space : $wp[0];
          $space['direction'] = $work['direction'];
          if ($harpies){
            $space = $harpies->forceWorker($test['workers'][0], $space, $opponent, true);
          }
          if (!$this->game->board->isSameSpace($space, $test['workers'][0]))
            $moveback = false;
        }
      }
      
      if ($moveback){
        $this->game->board->setPieceAt($top,$top);
        return;
      }
      $top['direction'] = $this->game->board->getDirection($bot,$top);
      $bot['works'] = [$top];
      $test['workers'] = [$bot];      
      $test['testOnly'] = true;
      $this->game->powerManager->applyPower(["argTeammateMove", "argOpponentMove"], [&$test]);
      Utils::cleanWorkers($test);
      $this->game->board->setPieceAt($top,$top);
      if (empty($test['workers']))
        return;
        
      // the bottom worker can move on the top one and no power in play forbids it, so we allow all moves for now but remember to require a move up next
      $this->game->log->addAction('CanMoveUp', [] , ['move' => 'up']);
      $canMoveUpLater = true;
      $arg['skippable'] = false;
    }
    
//    if (in_array($arg['ifPossiblePower'], [ARTEMIS, TRITON, ATALANTA])){
//      $test = $this->game->log->getLastAction('testMultipleMoves');
//      if ($test == null)
//      {
//        $test = $this->testMultipleMoves($arg);
//      }
//      elseif($test['state'] == 'testing')
//        $canMoveUp = false; // do nothing
//      elseif($test['state'] == 'true'){
//        if ($this->game->log->getLastAction('HasMovedUp') == null)
//          $arg['skippable'] = false;
//        $canMoveUp = true; 
//        $canMoveUpLater = !in_array($arg['ifPossiblePower'], [ARTEMIS]);
//      }
//    }

    
    if ($arg['ifPossiblePower'] == ATALANTA && $this->game->log->getLastAction('CanMoveUp') == null && $this->game->log->getLastAction('CannotMoveUp') == null){
      $canMoveUpHero = false;
      foreach($arg['workers'] as $worker){
        $this->game->board->setPieceAt($worker, $worker, 'dummy');
        $canMoveUpHero = $canMoveUpHero || $this->testMultipleMoves($arg, $worker);
        $this->game->board->setPieceAt($worker, $worker);
      }
      if ($canMoveUp)
        $this->game->log->addAction('CanMoveUp', [] , ['move' => 'up']);
      else 
        $this->game->log->addAction('CannotMoveUp', [] , ['move' => 'up']);
      if ($canMoveUpHero)
        $this->game->log->addAction('CanMoveUpHero', [] , ['move' => 'up']);
    }
    
    $couldmoveup = $this->game->log->getLastAction('CanMoveUp') != null;
    $couldmoveuphero = $this->game->log->getLastAction('CanMoveUpHero') != null;
    if ($arg['ifPossiblePower'] == ATALANTA && $this->game->log->getLastAction('HasMovedUp') == null && $couldmoveuphero){
      
      $moves = count($this->game->log->getLastActions(['move']));
      // remove moves that do not go up and do not allow to go up later
      if ($moves > 0 || $canMoveUp){
        Utils::filterWorks($arg, function ($space, $worker) use ($arg) {      
          $this->game->board->setPieceAt($worker, $worker, 'dummy');
          $res = $space['z'] > $worker['z'] || $this->testMultipleMoves($arg, $space);
          $this->game->board->setPieceAt($worker, $worker);
          return $res;
          });
      }
      
      $canMoveUpLater = true; // prevent later filtering
      
      if ($couldmoveup || $moves > 1)
        $arg['skippable'] = false;    
    }
    

    if ($arg['ifPossiblePower'] == BELLEROPHON){
      $canMoveUp = $this->canMoveUp($arg, 1, 1); // does not force to use the power
    }
    
    if (in_array($arg['ifPossiblePower'], [ACHILLES, ODYSSEUS]) && !$canMoveUp){
      $canMoveUp = $this->game->log->getLastAction('CanMoveUpHero') != null && $this->game->log->getLastAction('usedPower') != null;
    }
    

    
//    $this->game->notifyAllPlayers('message', 'later'. (!$canMoveUpLater) . ' (can' . $canMoveUp . ' last'. ($this->game->log->getLastAction('CanMoveUp') != null) . ') has' . ($this->game->log->getLastAction('HasMovedUp') == null), []);  

    if (!$canMoveUpLater && ($canMoveUp || $this->game->log->getLastAction('CanMoveUp') != null) && $this->game->log->getLastAction('HasMovedUp') == null) {
      if (in_array($arg['ifPossiblePower'], [SIREN, HERMES, CASTOR]))
        $arg['skippable'] = false;
    
      Utils::filterWorks($arg, function ($space, $worker) {
        return $space['z'] > $worker['z'];
      });
    }
    
    if (empty($arg['workers']) && !$arg['testOnly'] && $this->game->log->getLastAction('NotifRestart') == null && $arg['skippable'] == false){
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name2} could move up but cannot anymore. The turn must be restarted.'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name2' => $this->game->getActivePlayerName(), // opponent
      ]);
      
      $this->game->log->addAction('NotifRestart', [], ['hasmove' => 'up']);
    }
  }
  
  public function afterOpponentMove($worker, $work){
    if ($work['z'] > $worker['z'] && $this->game->log->getLastAction('HasMovedUp') == null){
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
      $this->game->log->addAction('HasMovedUp', [], ['hasmove' => 'up']);
    }
  }
  
}
