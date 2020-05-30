<?php


  
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
    $arg['workers'] = []


    $opponents = $this->game->playerManager->getOpponentsIds(); // TODO not sure how it works with 4 players... I want just one ID there
    $workers = $this->game->board->getPlacedActiveWorkers();
    
    
    if (count($workers) != 2)
      throw new BgaVisibleSystemException('Unexpected state in Nemesis.');

    // for each opponent, check if none worker neighbor Nemesis'    
    foreach($opponents as $opp){
      $oppworkers = $this->game->board->getPlacedWorkers($opp);
      $nb = count($oppWorkers);
      if ($nb < 2)
        throw new BgaVisibleSystemException('Unexpected state in Nemesis.');
      
      Utils::FilterWorkers($oppWorkers, function($opp) use ($workers) {
        return !$this->game->board->isNeighbour($workers[0], $opp) && !$this->game->board->isNeighbour($workers[1], $opp);
      });
      
      // if the condition is reached, allow the user to select two opponent workers
      if (count($oppWorkers) == $nb)
      {
        foreach($oppWorkers as $oppw)
        {
          $arg['workers'][] = $oppw;
          $arg['workers'][$oppw] = $oppWorkers; // TODO: c'est pas par référence ce truc j'espère
          Utils::FilterWorkersById($arg['workers'][$oppw], $oppw['id'], false);
        }
      }
    }
    
    Utils::cleanWorkers($arg);
  }


  public function usePower($action)
  {
    // Extract info from action
    $space1 = $action[0];
    $space2 = $action[1];

    // Get info about workers 
    $oppWorkers = [$this->game->board->getPieceAt($space1), $this->game->board->getPieceAt($space2)];
    $workers = $this->game->board->getPlacedActiveWorkers();

    if (count($workers) != 2)
      throw new BgaVisibleSystemException('Unexpected state in Nemesis.');
    
    $dest = [ $this->game->board->getCoords[$oppWorkers[0]],  $this->game->board->getCoords[$oppWorkers[1]] ];
    $oppdest = [ $this->game->board->getCoords[$workers[0]],  $this->game->board->getCoords[$workers[1]] ];

    // Exchange pieces
    self::DbQuery("UPDATE piece SET x = {$dest[0]['x']}, y = {$dest[0]['y']}, z = {$dest[0]['z']} WHERE id = {$workers[0]['id']}");
    self::DbQuery("UPDATE piece SET x = {$oppdest[0]['x']}, y = {$oppdest[0]['y']}, z = {$oppdest[0]['z']} WHERE id = {$oppWorkers[0]['id']}");
    $this->game->log->addForce($workers[0], $dest[0]);
    $this->game->log->addForce($oppWorkers[0], $oppdest[0]);
    
    
    // Notify
    $this->game->notifyAllPlayers('workerSwitched', clienttranslate('${power_name}: ${player_name} forces a swap with ${player_name2}'), [
      'i18n' => ['power_name'],
      'piece1' => $workers[0],
      'piece2' => $oppworkers[0],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($oppWorkers[0]['player_id'])->getName(),
    ]);
    
    
    self::DbQuery("UPDATE piece SET x = {$dest[1]['x']}, y = {$dest[1]['y']}, z = {$dest[1]['z']} WHERE id = {$workers[1]['id']}");
    self::DbQuery("UPDATE piece SET x = {$oppdest[1]['x']}, y = {$oppdest[1]['y']}, z = {$oppdest[1]['z']} WHERE id = {$oppWorkers[1]['id']}");
    $this->game->log->addForce($workers[1], $dest[1]);
    $this->game->log->addForce($oppWorkers[1], $oppdest[1]);
    
    $this->game->notifyAllPlayers('workerSwitched', clienttranslate('${power_name}: ${player_name} forces a swap with ${player_name2}'), [
      'i18n' => ['power_name'],
      'piece1' => $workers[1],
      'piece2' => $oppworkers[1],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($oppWorkers[0]['player_id'])->getName(),
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
  
}
