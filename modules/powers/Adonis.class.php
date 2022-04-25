<?php

class Adonis extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ADONIS;
    $this->name  = clienttranslate('Adonis');
    $this->title = clienttranslate('Devastatingly Handsome');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], choose one of your Workers and an opponent Worker. If possible, the Workers must be neighboring at the end of your opponent's next turn."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 25;

    $this->implemented = true;
  }

  /* * */


  public function stateAfterBuild()
  {
    return 'power';
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      foreach ($oppWorkers as $worker2) {
        $worker['works'][] = SantoriniBoard::getCoords($worker2, 0, true);
      }
    }
  }

  public function usePower($action)
  {
    // Get info about the two workers
    $adonisWorker = $this->game->board->getPiece($action[0]);
    $oppWorker = $this->game->board->getPiece($action[1]['id']);

    $this->game->log->addAction('usePowerAdonis', [], [
      'adonisWorkerId' => $adonisWorker['id'],
      'oppWorkerId' => $oppWorker['id'],
    ]);

    // Notify
    $oppPlayer = $this->game->playerManager->getPlayer($oppWorker['player_id']);
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: If possible, ${player_name2} (${coords2}) must end the next turn neighboring ${player_name} (${coords})'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(), // Adonis
      'player_name2' => $oppPlayer->getName(), // opponent
      'coords' => $this->game->board->getMsgCoords($adonisWorker),
      'coords2' => $this->game->board->getMsgCoords($oppWorker),
    ]);
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  /* * */

  public function getPowerData()
  {
    $powerData = $this->game->log->getLastAction('usePowerAdonis', $this->playerId);
    if ($powerData != null) {
      $powerData['adonisWorker'] = $this->game->board->getPiece($powerData['adonisWorkerId']);
      $powerData['oppWorker'] = $this->game->board->getPiece($powerData['oppWorkerId']);
    }
    return $powerData;
  }

    // $hermesMoveOther=true: return true if adonis can be reached through the allied worker and he can move away (i.e., the same-level connected component is not a single path with a single Adonis Neighbour, at one end)
    // if adonis can be reached without moving the other worker, the result does not matter
  public function testMultipleMoves(&$arg, $powerData, $start = null, $hermesMoveOther = false)
  {
    $visited = [];
    // possible params: 'init' = initial position; 'once' = one more move possible, 'stop' = no more move possible, 'conti' = can continue
    if ($start == null)
      $queue = [[$powerData['oppWorker'], 'init']];
    else
      $queue = [$start];
    $workers = $this->game->board->getPlacedActiveWorkers();
    
    // flags useful for HERMES
    $isAPath = true;
    $foundAdonis = false;
        
    while (!empty($queue)){
      $elem = array_pop($queue);
      $space = $elem[0];
      $param = $elem[1];
      
      if (!$hermesMoveOther && $this->game->board->isNeighbour($space, $powerData['adonisWorker']) && (!empty($visited) || $arg['skippable'] || $start != null))
        return true;
      
      $reach = $this->game->board->getNeighbouringSpaces($space, 'move');
      if ($param == 'stop' || $space['z'] == 3)
        $reach = [];
      if ($arg['ifPossiblePower'] == HERMES){
        Utils::filter($reach, function ($s) use ($space) {return $s['z'] == $space['z'];});
        // add workers as explored spaces
        if ($hermesMoveOther){
          foreach($workers as $sp){
            if ($sp['z'] == $space['z'] && $this->game->board->isNeighbour($sp,$space))
              $reach[] = $sp;
          }
          if ($this->game->board->isNeighbour($space, $powerData['adonisWorker']))
          {
            if (count($reach) > 1)
              return true; // Adonis neighbour space is not at the end of a path
            $foundAdonis = true;
          }
          if (count($reach) > 2)
            $isAPath = false; // there is room to move the other worker away
          if (!$isAPath && $foundAdonis)
            return true;
        }
      }
      foreach($reach as $sp){
        if (!empty(array_filter($visited, function ($s) use ($sp) {return $this->game->board->isSameSpace($s   ,$sp);})) || 
            !empty(array_filter($queue  , function ($s) use ($sp) {return $this->game->board->isSameSpace($s[0],$sp);})))
          continue;
        $nextparam = 'stop';
        if ($param == 'init' && $arg['ifPossiblePower'] == ARTEMIS)
          $nextparam = 'once';
        if ($arg['ifPossiblePower'] == TRITON && $this->game->board->isPerimeter($sp))
          $nextparam = 'conti';
        if ($arg['ifPossiblePower'] == HERMES || $arg['ifPossiblePower'] == ATALANTA)
          $nextparam = 'conti';
        $queue[] = [$sp, $nextparam];
      }
      
      $visited[] = $space;
    }
    return false;  
  }
  
  public function testHermesMove(&$arg, $powerData, $allowClassic){
  
    $workers = $this->game->board->getPlacedActiveWorkers();
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    $builds = [];
    foreach($workers as $worker){
      $builds[] = $this->game->board->getNeighbouringSpaces($worker);
    }
    
    // can skip
    if ($already && (!empty($builds[0]) || !empty($builds[1])))
      return true;
    
    // compute moves of the targetted worker satisfying the condition
    $works = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'move');
    $works = array_filter($works, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);});
    
    // can do a normal turn
    if ($allowClassic && !empty($works))
      return true;
    
    // can move the targetted worker only, at the same level, and neighbour Adonis 
    if ($this->testMultipleMoves($arg, $powerData))
      return true;
    
    // can move the other worker away, and the targetted worker towards Adonis
    return $this->testMultipleMoves($arg, $powerData, null, true);
  }
  
  
  public function testCastorMove(&$arg, $powerData)
  {        
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    $workers = $this->game->board->getPlacedActiveWorkers();
    
    if ($already){
      $builds = [];
      foreach($workers as $worker){
        $builds[] = $this->game->board->getNeighbouringSpaces($worker);
      }
      if (count($builds) == 1 and !empty($builds[0]))
        return true;
      if (count($builds) == 2 && !empty($builds[0]) && !empty($builds[1])){
        if (count($builds[0])>1 && count($builds[1]>1))
          return true;
        if (!$this->game->board->isSameSpace($builds[0][0],$builds[1][0]) || $builds[0][0]['z']<3)
          return true;
      }
    }
    
    $test = ['workers' => $arg['workers']];
    Utils::filterWorks($test, function ($space, $worker) use ($powerData, $arg, $already) {
      return ($worker['id'] == $powerData['oppWorker']['id'] && $this->game->board->isNeighbour($powerData['adonisWorker'], $space));
    });
    
    if (!empty($test['workers']))
      return true;
    
    if(count($workers) != 2)
      return false;
    
    if($workers[0]['id'] == $powerData['oppWorker']['id'])
      $otherworker = $workers[1];
    else
      $otherworker = $workers[0];
    
    if (!$this->game->board->isNeighbour($otherworker, $powerData['oppWorker']) || !$this->game->board->isNeighbour($otherworker, $powerData['adonisWorker']))
      return false;
    
    return !empty($this->game->board->getNeighbouringSpaces($otherworker, 'move'));  
  }
  
  public function existBuildNotInSpace(&$builds, $space)
  {
    foreach ($builds as $build){
      foreach ($build as $sp){
        if (!$this->game->board->isSameSpace($space, $sp))
          return true;
      }
    }
    return false;
  }
  
  public function testSirenMove(&$arg, $powerData, $siren, $onlyTestPower = false)
  {
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    $workers = $this->game->board->getPlacedActiveWorkers();
    $argPower = [];
    $siren->argUsePower($argPower);
    $builds = [];
    foreach($workers as $worker){
      $builds[] = $this->game->board->getNeighbouringSpaces($worker);
    }        
    if($workers[0]['id'] == $powerData['oppWorker']['id'])
      $otherworker = $workers[1];
    else
      $otherworker = $workers[0];
    $otherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'move');
    
    // compute moves of the targetted worker satisfying the condition
    $works = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'move');
    $works = array_filter($works, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);});
        
    // test if a normal turn can satisfy the condition
    if (!$onlyTestPower){
      if ( ($already && !empty($otherworks)) || !empty($works))
        return true;
    }
    
    // test if using the power can satisfy the condition
    if (count($argPower['workers']) == 1){
      if ($argPower['workers'][0]['id'] == $powerData['adonisWorker']['id']){
        // must move towards a space satisfying the condition
        if (!$this->game->board->isNeighbour($powerData['oppWorker'], $argPower['workers'][0]['works'][0]))
          return false;
        // power freed a build (second use of power cannot free a build)
        if ($this->game->board->isNeighbour($workers[0], $powerData['adonisWorker']) || $this->game->board->isNeighbour($workers[1], $powerData['adonisWorker']))
          return true;
        // a build was available already
        if ($this->existBuildNotInSpace($builds, $argPower['workers'][0]['works'][0]))
          return true;
      }
      else{
        // using the power once allows a build
        if ($already && ($this->existBuildNotInSpace($builds, $argPower['workers'][0]['works'][0]) || $this->game->board->isNeighbour($argPower['workers'][0], $workers[0]) || $this->game->board->isNeighbour($argPower['workers'][0], $workers[1])))
          return true;
        // can use a second power, respects the condition and allows a build
        $adWorker = [$powerData['adonisWorker']];
        if ($this->game->board->getSpaceBehind($argPower['workers'][0]['works'][0], $argPower['workers'][0], $adWorker) != null){       
          if ($this->game->board->isNeighbour($argPower['workers'][0], $powerData['oppWorker'])  && 
                 (!empty($builds[0]) || !empty($builds[1]) || $already || $this->game->board->isNeighbour($argPower['workers'][0], $otherworker)))
            return true;
        }
      }
    }
    if (count($argPower['workers']) == 2){
      if ($argPower['workers'][0]['id'] == $powerData['adonisWorker']['id']){
        $targetPower = $argPower['workers'][0];
        $otherPower = $argPower['workers'][1];
      }
      else{
        $targetPower = $argPower['workers'][1];
        $otherPower = $argPower['workers'][0];
      }
      
      // use the power only on the other worker
      if ($already && ($this->existBuildNotInSpace($builds, $otherPower['works'][0]) || $this->game->board->isNeighbour($otherPower, $workers[0]) || $this->game->board->isNeighbour($otherPower, $workers[1])))
        return true;
      
      // use the power on the targetted worker       
      if ($this->game->board->isNeighbour($targetPower['works'][0], $powerData['oppWorker'])){
        // this is enough to get a build
        if ($this->existBuildNotInSpace($builds, $targetPower['works'][0]) || $already || $this->game->board->isNeighbour($targetPower, $otherworker))
          return true;
        // using twice the power frees a build
        if ($this->game->board->isNeighbour($otherPower, $workers[0]) || $this->game->board->isNeighbour($otherPower, $workers[1]))
          return true;
      }
    }
    
    return false;
  }


  public function testTerpsiMove(&$arg, $powerData)
  {
    $workers = $this->game->board->getPlacedActiveWorkers();
    if($workers[0]['id'] == $powerData['oppWorker']['id'])
      $otherworker = $workers[1];
    else
      $otherworker = $workers[0];
    $otherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'move');
    
    $test = ['workers' => $arg['workers']];
    Utils::filterWorks($test, function ($space, $worker) use ($powerData, $arg) {
      return ($worker['id'] == $powerData['oppWorker']['id'] && $this->game->board->isNeighbour($powerData['adonisWorker'], $space));
    });
    
    // both can move without walking on each other, so can then build
    if (!empty($test['workers']) && !empty($otherworks))
      return true;
    
    // the target can move, free a space for the other
    if (!empty($test['workers']) && $this->game->board->isNeighbour($powerData['oppWorker'], $otherworker) && $powerData['oppWorker']['z'] <= $otherworker['z']+1){
      foreach($test['workers'][0] as $space){
        // both can build on the space newly freed (cannot be a level 3)
        if ($this->game->board->isNeighbour($space, $otherworker))
          return true;
        // the first to move can build and the other can always build on the freed space
        if (!empty($this->game->board->getNeighbouringSpaces($space, 'build')))
          return true;
      }
    }
    
    // vice versa
    if (!empty($otherworks) && $this->game->board->isNeighbour($powerData['oppWorker'], $otherworker) && $powerData['oppWorker']['z']+1 >= $otherworker['z']){
      foreach($otherworks as $space){
        // both can build on the space newly freed (cannot be a level 3)
        if ($this->game->board->isNeighbour($space, $powerData['oppWorker']))
          return true;
        // the first to move can build and the other can always build on the freed space
        if (!empty($this->game->board->getNeighbouringSpaces($space, 'build')))
          return true;
      }
    }
    
    // the target can win
    foreach($test['workers'][0] as $space){
      if ($space['z'] == 3)
        return true;
    }

    // the other can win     
    if ($this->game->board->isNeighbour($powerData['oppWorker'], $powerData['adonisWorker'])){
      foreach($otherworks as $space){
        if ($space['z'] == 3)
          return true;
      }
    }
    
    return false;
  }
  
  public function testHeroPower(&$arg, $powerData)
  {
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    
    // compute possible future moves for the other worker to know if it can be the moving worker next
    $workers = $this->game->board->getPlacedActiveWorkers();
    if (count($workers) == 2) {
      if($workers[0]['id'] == $powerData['oppWorker']['id'])
        $otherworker = $workers[1];
      else
        $otherworker = $workers[0];
      $otherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'move');
    }
    
    // compute moves of the targetted worker satisfying the condition
    $works = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'move');
    Utils::filter($works, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);});
    
    $okWithoutPower = !empty($works) || ($already && !empty($otherworks));
    
    if ($arg['ifPossiblePower'] == BELLEROPHON){
      $works = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'move', [BELLEROPHON]);
      $works = array_filter($works, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);});
      $otherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'move', [BELLEROPHON]);
      $okWithPower = !empty($works)  || ($already && !empty($otherworks));
    }
    elseif ($arg['ifPossiblePower'] == ACHILLES){
      if (!$okWithoutPower)
        $okWithPower = false;
      else{
        $Bworks = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'build');
        $Botherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'build');
        if ($already && empty($works) && count($otherworks) == 1)
          $okWithPower = ($otherworks[0]['z'] <= $otherworker['z']+1) || (count($Botherworks) > 1);
        elseif ($already && count($otherworks) + count($works) > 1)
          $okWithPower = true;
        elseif (count($works) >= 1)
          $okWithPower = ($works[0]['z'] <= $powerData['oppWorker']['z']+1) || (count($Bworks) > 1);
        else
          throw new BgaVisibleSystemException("Unexpected state in Adonis vs Achilles");
      }
    }
    elseif($arg['ifPossiblePower'] == ODYSSEUS){
      // TODO
      // check which corners are free
      // check if $already -> can teleport the target worker and move next to a corner
      // check if can teleport the other worker and this frees a space towards the target or a corner if $already
    }
    elseif($arg['ifPossiblePower'] == ATALANTA){
      $okWithPower = $this->testMultipleMoves($arg, $powerData);
    }
    else
      throw new BgaVisibleSystemException("Unexpected hero power vs Adonis");
      
    return [$okWithPower, $okWithoutPower];    
  }


  public function argOpponentUsePower(&$arg)
  {
    $powerData = $this->getPowerData();
    if ($powerData == null) {
      return;
    }
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    
    // compute possible future moves for the other worker to know if it can be the moving worker next
    $workers = $this->game->board->getPlacedActiveWorkers();
    if (count($workers) == 2) {
      if($workers[0]['id'] == $powerData['oppWorker']['id'])
        $otherworker = $workers[1];
      else
        $otherworker = $workers[0];
      $otherworks = $this->game->board->getNeighbouringSpaces($otherworker, 'move');
    }
    
    // compute moves of the targetted worker satisfying the condition
    $works = $this->game->board->getNeighbouringSpaces($powerData['oppWorker'], 'move');
    $works = array_filter($works, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);});
    
    if ($arg['power'] == JASON){
      // using the power would prevent fulfilling the condition, this is the only thing that should change as the power cannot be forced to be used
      if (!$already && !empty($works))
        $arg['workers'] = [];
    }
    
    if ($arg['power'] == THESEUS){
      if ($already)
        Utils::filterWorks($arg, function ($space, $worker) use ($powerData) {
          return !$this->game->board->isSameSpace($powerData['adonisWorker'], $space);
        });
    }
    
    if ($arg['power'] == PROTEUS){
      $move = $this->game->log->getLastMove()['from'];
      $neighb = $this->game->board->isNeighbour($powerData['adonisWorker'], $move);
      if ($already && !$neighb)
        Utils::filterWorkersById($arg, $powerData['oppWorker']['id'], false);
      if (!$already && $neighb)
        Utils::filterWorkersById($arg, $powerData['oppWorker']['id'], true);
      if (!$already && !$neighb)
        $arg['workers'] = []; // should never happen
    }
    
    if ($arg['power'] == ODYSSEUS){ // does not prevent deadends for now
      $alreadytested = $this->game->log->getLastAction('adonisTest');
      if ($alreadytested != null)
        $withOrWithoutHero = $alreadytested['possible'];
      else{
        $withOrWithoutHero = $this->testHeroPower($arg, $powerData);
        $this->game->log->addAction('adonisTest', [],  ['possible' => $withOrWithoutHero]);
      }
      
      // TODO            
    }
    
    if ($arg['power'] == SCYLLA){
      $move = $this->game->log->getLastMove()['from'];
      $neighb = $this->game->board->isNeighbour($powerData['oppWorker'], $move);
      if ($already && !$neighb)
        Utils::filterWorkersById($arg, $powerData['adonisWorker']['id'], false);
      if (!$already && $neighb)
        Utils::filterWorkersById($arg, $powerData['adonisWorker']['id'], true);
      if (!$already && !$neighb)
        $arg['workers'] = []; // should never happen
    }
    
    if ($arg['power'] == CHARON){
      $test = $arg;
      // keep only works satisfying the condition
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      Utils::filterWorks($test, function ($workerAd, $worker) use ($powerData, $accessibleSpaces, $already) {
        // remove impossible action (no possible move afterwards)
        $target = $this->game->board->getSpaceBehind($workerAd, $worker, $accessibleSpaces);
        $spaces = $this->game->board->getNeighbouringSpaces($worker, 'move');
        $spaces = array_values(array_filter($spaces, function ($s) use ($target) {return !$this->game->board->isSameSpace($s, $target);}));
        if ($worker['z'] >= $workerAd['z'] - 1)
          $spaces[] = $workerAd;
        if (empty($spaces))
          return false;
        
        // other worker and do not target Adworker: ok iff already
        if ($worker['id'] != $powerData['oppWorker']['id'] && $workerAd['id'] != $powerData['adonisWorker']['id'])
          return $already;
        // other worker and target adonisWorker: ok iff force to neighbor
        if ($worker['id'] != $powerData['oppWorker']['id'] && $workerAd['id'] == $powerData['adonisWorker']['id'])
          return $this->game->board->isNeighbour($target, $powerData['oppWorker']);
        // oppworker and do not target Adworker: ok iff can end up next to adonisWorker
        if ($worker['id'] == $powerData['oppWorker']['id'] && $workerAd['id'] != $powerData['adonisWorker']['id'])
          return !empty(array_filter($spaces, function ($s) use ($powerData) {return $this->game->board->isNeighbour($s, $powerData['adonisWorker']);}));
        // oppworker and target adonisWorker: ok iff can end up next to target
        if ($worker['id'] == $powerData['oppWorker']['id'] && $workerAd['id'] == $powerData['adonisWorker']['id'])
          return !empty(array_filter($spaces, function ($s) use ($target) {return $this->game->board->isNeighbour($s, $target);}));
      });
      
      // test if skipping power allows the condition
      $test['skippable'] = (($already && !empty($otherworks)) || !empty($works));
      // if test has a possible option, restrict the moves.
      if (!empty($test['workers']) || $test['skippable'])
        $arg = $test;
    }
    
    if ($arg['power'] == SIREN){
      if (!$already)
        $arg['skippable'] = false;    
    }
    
  }
  
  
  public function argOpponentBuild(&$arg)
  {
    $powerData = $this->getPowerData();
    if ($powerData == null) {
      return;
    }
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    
    if ($arg['ifPossiblePower'] == PROMETHEUS){
      $spaces = $this->game->board->getNeighbouringSpaces($powerData['oppWorker']);
      $neighb = [];
      $lowneighb = [];
      $sameneighb = [];
      foreach($spaces as $space){
        if ($this->game->board->isNeighbour($space,$powerData['adonisWorker']) && $space['z'] <= $powerData['oppWorker']['z'] + 1)
        {
          $neighb[] = $space;
          if ($space['z'] <  $powerData['oppWorker']['z'])
            $lowneighb[] = $space;
          if ($space['z'] == $powerData['oppWorker']['z'])
            $sameneighb[] = $space;
        }
      }
      if (!$already){
        Utils::filterWorkersById($arg, $powerData['oppWorker']['id']);
        if (empty($neighb) || !empty($lowneighb) || count($sameneighb) > 1 )
          return;
        if (empty($sameneighb))
        {
          $arg['workers'] = [];
          return;
        }
                
        Utils::filterWorks($arg, function ($space, $worker) use ($sameneighb) {
          return !$this->game->board->isSameSpace($sameneighb[0], $space);
        });
      }
      else{
        if (empty($sameneighb)){
          Utils::filterWorkersById($arg, $powerData['oppWorker']['id'], false);
          return;
        }
        if (!empty($lowneighb) || count($sameneighb) > 1 )
          return;
             
        Utils::filterWorks($arg, function ($space, $worker) use ($sameneighb, $powerData) {
          return !$this->game->board->isSameSpace($sameneighb[0], $space) || $worker['id'] != $powerData['oppWorker']['id'];
        });
      }
    }
    if ($arg['ifPossiblePower'] == ACHILLES){ // does not prevent deadends for now
      $alreadytested = $this->game->log->getLastAction('adonisTest');
      if ($alreadytested != null)
        $withOrWithoutHero = $alreadytested['possible'];
      else{
        $withOrWithoutHero = $this->testHeroPower($arg, $powerData);
        $this->game->log->addAction('adonisTest', [],  ['possible' => $withOrWithoutHero]);
      }
      
      if ($withOrWithoutHero[1] && !$withOrWithoutHero[0])
        $arg['workers'] = [];
      
      if ($withOrWithoutHero[1] && !$already)
        Utils::filterWorkersById($arg, $powerData['oppWorker']['id']);
    }
    
  }

  public function argOpponentMove(&$arg)
  {
    $powerData = $this->getPowerData();
    if ($powerData == null) {
      return;
    }

    $test = ['workers' => $arg['workers']];
    $already = $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
    
    $alreadytested = $this->game->log->getLastAction('adonisTest');
    $testedHero = false;
    $testedNoHero = false;
    if ($alreadytested != null && is_array($alreadytested['possible'])){
      $testedHero = $alreadytested['possible'][0];
      $testedNoHero = $alreadytested['possible'][1];
      if ($testedNoHero)
        $alreadytested['possible'] = true;
      elseif (!$testedHero)
        $alreadytested['possible'] = false;
      elseif (in_array($arg['ifPossiblePower'], [ACHILLES, ODYSSEUS]))
        $alreadytested['possible'] = !empty($this->game->log->getLastActions(['force','build','move'])); // if used the power, must respect the condition
      elseif (in_array($arg['ifPossiblePower'], [BELLEROPHON]))
        $alreadytested['possible'] = false;
      elseif (in_array($arg['ifPossiblePower'], [ATALANTA])){
        $alreadytested['possible'] = count($this->game->log->getLastActions(['move'])) > 1;
      }
      else
        throw new BgaVisibleSystemException("Unexpected hero power vs Adonis argOpponentMove");
        
    }
    if ($alreadytested != null && ($alreadytested['possible'] == false && !$testedHero))
      return;
      
    $alreadytested = ($alreadytested == null) ? false : $alreadytested['possible'];
    
    // get opponent power if relevant
    $oppPower = null;
    $opponent = $this->game->playerManager->getPlayer($powerData['oppWorker']['player_id']);
    foreach ($opponent->getPowers() as $power) {
      if (in_array($power->getId(), [CHARYBDIS, SIREN]))
        $oppPower = $power;
    }
    
    // complex powers involving multiple worker moves in a turn.
    // At the firt pass of the turn, compute if the condition can be satisfied. If it cannot, we terminate.
    // If it can, we compute here if the move can be passed or not
    if (in_array($arg['ifPossiblePower'], [ARTEMIS, TRITON, CASTOR, TERPSICHORE, SIREN, HERMES, ATALANTA, BELLEROPHON, ACHILLES, ODYSSEUS])){
      if (!$alreadytested && !$testedHero){
        if ($arg['ifPossiblePower'] == CASTOR)
          $alreadytested = $this->testCastorMove($arg, $powerData);
        elseif ($arg['ifPossiblePower'] == BELLEROPHON){
          $alreadytested = $this->testHeroPower($arg, $powerData);
          $testedHero = $alreadytested[0];
          $testedNoHero = $alreadytested[1];
        }
        elseif ($arg['ifPossiblePower'] == ATALANTA){
          $alreadytested = $this->testHeroPower($arg, $powerData);
          $testedHero = $alreadytested[0];
          $testedNoHero = $alreadytested[1];
        }
        elseif ($arg['ifPossiblePower'] == ACHILLES || $arg['ifPossiblePower'] == ODYSSEUS)
          throw new BgaVisibleSystemException("Unexpected state in Adonis: ACHILLES or ODYSSEUS have incoherent data vs Adonis");
        elseif($arg['ifPossiblePower'] == TERPSICHORE)
          $alreadytested = $this->testTerpsiMove($arg, $powerData);
        elseif($arg['ifPossiblePower'] == SIREN)
          $alreadytested = $this->testSirenMove($arg, $powerData, $oppPower);
        elseif($arg['ifPossiblePower'] == HERMES)
          $alreadytested = $this->testHermesMove($arg, $powerData, $oppPower);
        else
          $alreadytested = $this->testMultipleMoves($arg, $powerData);
        $this->game->log->addAction('adonisTest', [],  ['possible' => $alreadytested]);
        
        // for hero, revert $alreadytested to a single boolean
        if ($arg['ifPossiblePower'] == BELLEROPHON)
          $alreadytested = $testedNoHero;
        if ($arg['ifPossiblePower'] == ATALANTA)
          $alreadytested = $testedNoHero ? true : (!$testedHero? false : count($this->game->log->getLastActions(['move'])) > 1 );
      }
      if (!$alreadytested && !$testedHero){
        $adonisPlayer = $this->game->playerManager->getPlayer($powerData['adonisWorker']['player_id']);
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: It is not possible for ${player_name2} (${coords2}) to end this turn neighboring ${player_name} (${coords})'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'player_name' => $adonisPlayer->getName(), // Adinois
          'player_name2' => $this->game->getActivePlayerName(), // opponent
          'coords' => $this->game->board->getMsgCoords($powerData['adonisWorker']),
          'coords2' => $this->game->board->getMsgCoords($powerData['oppWorker']),
        ]);
        // Discard immediately to prevent duplicate notifications
        $this->game->powerManager->removePower($this, 'hero');
        return;
      }
        
      // all powers excet Siren cannot move workers after skipping
      if ($arg['ifPossiblePower'] == SIREN)
        $arg['skippable'] = $this->testSirenMove($arg, $powerData, $oppPower, true);
      if ($arg['ifPossiblePower'] == ATALANTA)
        $arg['skippable'] = $arg['skippable'] && ($already || (!$alreadytested && count($this->game->log->getLastActions(['move'])) == 1));
      else
        $arg['skippable'] = $arg['skippable'] && $already;
    }
    
    
    // if the power does not force Adonis, or does not move both workers, prevent the non-targetted worker to move if the condition is not satisfied 
    if (!$already && !in_array($arg['ifPossiblePower'], [APOLLO, MINOTAUR, PROTEUS, CASTOR, TERPSICHORE, SCYLLA, HERMES]) && ($alreadytested || !in_array($arg['ifPossiblePower'], [BELLEROPHON, ATALANTA]) )){
      Utils::filterWorkersById($test, [$powerData['oppWorker']['id'], $powerData['adonisWorker']['id']]);
    }
  
    // keep only the works respecting the condition
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    Utils::filterWorks($test, function ($space, $worker) use ($powerData, $arg, $already, $accessibleSpaces, $oppPower, $alreadytested) {
      if (in_array($arg['ifPossiblePower'],  [APOLLO, MINOTAUR]) && $worker['id'] != $powerData['oppWorker']['id']){
        $forced = ($arg['ifPossiblePower'] == APOLLO) ? $worker :  $this->game->board->getSpaceBehind($worker, $space, $accessibleSpaces);
        if (!$already)
          return $this->game->board->isNeighbour($powerData['oppWorker'], $forced) && $this->game->board->isSameSpace($powerData['adonisWorker'], $space);
        return $this->game->board->isNeighbour($powerData['oppWorker'], $forced) || !$this->game->board->isSameSpace($powerData['adonisWorker'], $space);
      }
      if ($arg['ifPossiblePower'] == CHARYBDIS && $worker['id'] == $powerData['oppWorker']['id']){
        $tel = $oppPower->whirlpooledSpace($space);
        $target = empty($tel) ? $space : $tel[0];
        return $this->game->board->isNeighbour($powerData['adonisWorker'], $target);
      }
      if ($arg['ifPossiblePower'] == HERMES && $space['z'] == $worker['z'])
        return true; // keep all moves using the power
      if ($arg['ifPossiblePower'] == HERMES && $worker['id'] != $powerData['oppWorker']['id'])
        return $already; // can change level only if $already
      if ($arg['ifPossiblePower'] == ATALANTA && $worker['id'] == $powerData['oppWorker']['id'])
        return $this->testMultipleMoves($arg, $powerData, [$space, 'conti']); 
      if ($arg['ifPossiblePower'] == ATALANTA && $worker['id'] != $powerData['oppWorker']['id'])
        return $already || (!$alreadytested && !$arg['skippable']); 
      if ($arg['ifPossiblePower'] == BELLEROPHON && $space['z'] == $worker['z']+2)
        return ($already && $worker['id'] != $powerData['oppWorker']['id']) || ($worker['id'] == $powerData['oppWorker']['id'] && $this->game->board->isNeighbour($powerData['adonisWorker'], $space)); // must respect the condition if the power is used
      if ($arg['ifPossiblePower'] == BELLEROPHON && $space['z'] <= $worker['z']+1 && !$alreadytested)
        return true; // must respect the condition only if the power is used
      if ($arg['ifPossiblePower'] == PROTEUS && !$already && $worker['id'] != $powerData['oppWorker']['id'])
        return $this->game->board->isNeighbour($powerData['adonisWorker'], $worker);
      if ($arg['ifPossiblePower'] == SCYLLA && !$already && $worker['id'] != $powerData['oppWorker']['id'])
        return $this->game->board->isNeighbour($powerData['oppWorker'], $worker);
      if ($arg['ifPossiblePower'] == SCYLLA && $worker['id'] == $powerData['oppWorker']['id'])
        return $this->game->board->isNeighbour($powerData['adonisWorker'], $worker) || $this->game->board->isNeighbour($powerData['adonisWorker'], $space);
      if ($arg['mayMoveAgain'] == ARTEMIS && $worker['id'] == $powerData['oppWorker']['id']){
        return $this->testMultipleMoves($arg, $powerData, [$space, 'once']);
      }
      if ($arg['mayMoveAgain'] == TRITON  && $worker['id'] == $powerData['oppWorker']['id']){
        if ($this->game->board->isPerimeter($space) && $space['z'] >= $worker['z'] - 1)
          return true;
        return $this->testMultipleMoves($arg, $powerData, [$space, ($this->game->board->isPerimeter($space) ? 'conti' : 'stop')]);
      }
       
      return ($worker['id'] != $powerData['oppWorker']['id'] || $this->game->board->isNeighbour($powerData['adonisWorker'], $space) 
                    || $this->game->board->isSameSpace($powerData['adonisWorker'], $space)) &&
             ($worker['id'] != $powerData['adonisWorker']['id'] || $this->game->board->isNeighbour($powerData['oppWorker'], $space));
    });
    
    // prevent powers from killing Adonis's target worker, or accept if there is a win before
    if ($arg['ifPossiblePower'] == BIA){
      Utils::filterWorks($test, function ($space, $worker) use ($powerData) {
        return ($powerData['adonisWorker']['x'] != 2*$space['x'] - $worker['x']) || ($powerData['adonisWorker']['y'] != 2*$space['y'] - $worker['y']) || $space['z'] == 3;
      });
    }
    
    if ($arg['ifPossiblePower'] == MEDUSA){
      $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId, true);
      $killerIds = [];
      foreach ($oppWorkers as $oppw){
        if ($this->game->board->isNeighbour($powerData['adonisWorker'], $oppw) && $oppw['z'] > $powerData['adonisWorker']['z'])
          $killerIds[] = $oppw['id'];
      }
      if (count($killerIds) > 1)
        Utils::filterWorks($test, function ($space, $worker){
          return ($space['z'] == 3);
        });
      else
        Utils::filterWorks($test, function ($space, $worker) use ($powerData, $killerIds) {
          if (!empty($killerIds) && $worker['id'] != $killerIds[0])
            return ($space['z'] == 3);
          else
            return (!$this->game->board->isNeighbour($powerData['adonisWorker'], $space) || $space['z'] == 3 || $space['z'] <= $powerData['adonisWorker']['z'] );
        });
    }
    
    // only keep non-winning moves where a following build is legal
    if (in_array($arg['ifPossiblePower'], [IRIS, APOLLO, PROTEUS, CHARYBDIS, SCYLLA])){
      Utils::filterWorks($test, function ($space, $worker) use ($arg, $oppPower, $powerData, $already) {
        
        if ($arg['ifPossiblePower'] == SCYLLA){
          if ($this->game->board->isNeighbour($space,$powerData['adonisWorker']))
            return true; // will neighbour a free space whether the power is used or not
          if ($already && $worker['id'] != $powerData['oppWorker']['id'])
            return true; // the power is not mandatory
        }
        if ($arg['ifPossiblePower'] == APOLLO){
          if (empty($this->game->board->getPiecesAt($space)))
            return true;
        }
        if ($arg['ifPossiblePower'] == IRIS){
          if ($this->game->board->isNeighbour($space,$worker))
            return true;
        }
        if ($arg['ifPossiblePower'] == CHARYBDIS){
          $tel = $oppPower->whirlpooledSpace($space);
          if (empty($tel))
            return true;
          $space = $tel[0];
        }
        
        $temp = $worker;
        $temp['x'] = $space['x'];
        $temp['y'] = $space['y'];
        return !empty($this->game->board->getNeighbouringSpaces($temp)) || $space['z'] == 3;
      });
    }
    
    // prevent wins when the condition is not satisfied
    if (in_array($arg['ifPossiblePower'], [TERPSICHORE])){
      Utils::filterWorks($test, function ($space, $worker) use ($arg, $oppPower, $powerData, $already) {
        return ($already || $worker['id'] == $powerData['oppWorker']['id'] || $space['z'] < 3);
      });
    }
    
    
    if (empty($test['workers']) && !$alreadytested && !$testedHero) {
      // Notify
      $adonisPlayer = $this->game->playerManager->getPlayer($powerData['adonisWorker']['player_id']);
      $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: It is not possible for ${player_name2} (${coords2}) to end this turn neighboring ${player_name} (${coords})'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $adonisPlayer->getName(), // Adinois
        'player_name2' => $this->game->getActivePlayerName(), // opponent
        'coords' => $this->game->board->getMsgCoords($powerData['adonisWorker']),
        'coords2' => $this->game->board->getMsgCoords($powerData['oppWorker']),
      ]);
      // Discard immediately to prevent duplicate notifications
      $this->game->powerManager->removePower($this, 'hero');
    } else {
      // Allow skip only if condition is already satisfied
      $arg['workers'] = $test['workers'];
      if ($arg['skippable'] && !$alreadytested && !$testedHero) {
        $arg['skippable'] = $arg['skippable'] && $already;
      }
      if (!$arg['skippable'] && $alreadytested && empty($test['workers'])){
        $adonisPlayer = $this->game->playerManager->getPlayer($powerData['adonisWorker']['player_id']);
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name2} could have satisfied the condition required but cannot anymore. The turn must be restarted.'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name2' => $this->game->getActivePlayerName(), // opponent
      ]);
      }
    }
  }

  // Adonis discard must happen after opponent's turn
  public function preEndPlayerTurn()
  {
  }

  public function preEndOpponentTurn()
  {
    if ($this->getPowerData() != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }
}
