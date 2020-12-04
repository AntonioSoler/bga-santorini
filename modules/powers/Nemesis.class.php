<?php

class Nemesis extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = NEMESIS;
    $this->name  = clienttranslate('Nemesis');
    $this->title = clienttranslate('Goddess of Retribution');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If none of an opponent's Workers neighbor yours, you may force both of your Workers to spaces occupied by two of an opponent's Workers, and vice versa."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 42;

    $this->implemented = true;
  }


  public function stateAfterBuild()
  {
    $arg = [];
    $this->argUsePower($arg);
    return (count($arg['workers']) > 0)? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach($arg['workers'] as &$worker)
    {
      $worker['works'] = [];
    }
	$workers = $this->game->board->getPlacedActiveWorkers();
	if (count($workers) != 2)
	  throw new BgaVisibleSystemException('Unexpected state in Nemesis.');

	$actions = $this->game->log->getLastWorks(['force'], null, 2);

	# choose second exchange
	if (count($actions) > 0)
	{		
	    $arg['skippable'] = false;
		if (count($actions) != 2)
		    throw new BgaVisibleSystemException('Unexpected log in Nemesis.');
		    
		$lastoppw = $actions[0]['pieceId'];
		$lastw = $actions[1]['pieceId'];
	
		$opponent = $this->game->board->getPiece($lastoppw)['player_id'];	
	    $oppWorkers = $this->game->board->getPlacedWorkers($opponent);
	    
	    Utils::filterWorkersById($oppWorkers, $lastoppw, false);
	    Utils::filterWorkersById($workers, $lastw, false);
	    
	    
		if (count($workers) != 1)
		    throw new BgaVisibleSystemException('Unexpected state in Nemesis.');
	    
	    $arg['workers'] = $workers;
	    $arg['workers'][0]['works'] = $oppWorkers;
	
	}
	else
	{
		$opponents = $this->game->playerManager->getOpponentsIds();

		

		// for each opponent, check if no worker neighbors Nemesis'    
		foreach($opponents as $opp){
		  $oppWorkers = $this->game->board->getPlacedWorkers($opp);
		  $nb = count($oppWorkers);
		  if ($nb < 2)
		    throw new BgaVisibleSystemException('Unexpected state in Nemesis.');
		  
		  Utils::FilterWorkers($oppWorkers, function($opp) use ($workers) {
		    return !$this->game->board->isNeighbour($workers[0], $opp) && !$this->game->board->isNeighbour($workers[1], $opp);
		  });
		  
		  // if the condition is reached, allow the user to select an opponent worker
		  if (count($oppWorkers) == $nb)
		  {
		    foreach($arg['workers'] as &$worker)
		    {
				foreach($oppWorkers as $oppW)
				{
					$worker['works'][] = $oppW;
				}
		    }
		  }
		}	
	}
        
    Utils::cleanWorkers($arg);
  }


  public function usePower($action)
  {
    // Get info about workers 
    $worker = $this->game->board->getPiece($action[0]);
    $oppWorker = $this->game->board->getPiece($action[1]['id']);
    

    $mySpace =  $this->game->board->getCoords($worker);
    $oppSpace = $this->game->board->getCoords($oppWorker);

	if (count($this->game->log->getLastWorks(['force'], null, 2)) > 1 ) 
		$stats = [[$this->playerId, 'usePower']]; # TODO wrong stat number, weird...
	else 
		$stats = [];
	$this->game->board->setPieceAt($worker, $oppSpace);
	$this->game->log->addForce($worker, $oppSpace, $stats);
	$this->game->board->setPieceAt($oppWorker, $mySpace);
	$this->game->log->addForce($oppWorker, $mySpace);

	// Notify force
	$this->game->notifyAllPlayers('workerMovedInstant', $this->game->msg['powerForce'], [
	'i18n' => ['power_name', 'level_name'],
	'piece' => $worker,
	'space' => $oppSpace,
	'power_name' => $this->getName(),
	'player_name' => $this->game->getActivePlayerName(),
	'player_name2' => $this->game->getActivePlayerName(),
	'level_name' => $this->game->levelNames[intval($oppSpace['z'])],
	'coords' => $this->game->board->getMsgCoords($worker, $oppSpace),
	]);

	$this->game->notifyAllPlayers('workerMovedInstant', $this->game->msg['powerForce'], [
	'i18n' => ['power_name', 'level_name'],
	'piece' => $oppWorker,
	'space' => $mySpace,
	'power_name' => $this->getName(),
	'player_name' => $this->game->getActivePlayerName(),
	'player_name2' => $this->game->playerManager->getPlayer($oppWorker['player_id'])->getName(),
	'level_name' => $this->game->levelNames[intval($mySpace['z'])],
	'coords' => $this->game->board->getMsgCoords($oppWorker, $mySpace),
	]);
	

    
   }

   public function stateAfterUsePower()
   {
     return count($this->game->log->getLastWorks(['force'], null, 3)) > 2 ? 'endturn' : 'power'; 
   }

   public function stateAfterSkipPower()
   {
     return 'endturn';
   }
  
}
