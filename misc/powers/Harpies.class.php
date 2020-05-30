<?php

class Harpies extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HARPIES;
    $this->name  = clienttranslate('Harpies');
    $this->title = clienttranslate('Winged Menaces');
    $this->text  = [
      clienttranslate("Opponent's Turn: Each time an opponent's Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */
}


// TODO: use it in SantoriniPower.class.php
  public function afterOpponentMove($worker, $work)
  {
    $oppworkers = $this->game->board->getPlacedOpponentWorkers();
    $test = Utils::filterWorkersById($oppworkers, $worker['id']);
    if (count($test) == 0)
      return; // this was not an opponent worker 
    
    // currently, no power affects this series of forces as Tartarus is only a 2-player god
    while (true){
      // Must be a free space behind
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      $space = $this->game->board->getSpaceBehind($worker, $work, $accessibleSpaces);
      if (is_null($space) || $space['z'] > $worker['z'])
        return;
      
      
      self::DbQuery("UPDATE piece SET x = {$space['x']}, y = {$space['y']}, z = {$space['z']} WHERE id = {$worker['id']}");
      $this->game->log->addForce($worker, $space);

      // Notify
      $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name}'), [
        'i18n' => ['power_name', 'level_name'],
        'piece' => $worker,
        'space' => $space,
        'power_name' => $this->getName(),
        'player_name2' => $this->game->getActivePlayerName(),
        'player_name' => $this->game->playerManager->getPlayer($this->playerId)->getName(),
        'level_name' => $this->game->levelNames[intval($space['z'])],
      ]);  
        
    }  
  }
