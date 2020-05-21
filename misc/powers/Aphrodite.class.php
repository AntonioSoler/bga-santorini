<?php

class Aphrodite extends Power
{
  public static function getId() {
    return APHRODITE;
  }

  public static function getName() {
    return clienttranslate('Aphrodite');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Love');
  }

  public static function getText() {
    return [
      clienttranslate("Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers.  TODO: Currently, if you do not respect the condition, you loose.")
    ];
  }

  public static function getPlayers() {
    return [2, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS, URANIA];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */
  
  
  
  // TODO: Description: warn that you loose if you do not respect the condition
  
  
  $workerForcedIds;
  
  public function startOpponentTurn() { // TODO: name?
    $this->workerForcedIds = [];
    
    $aphWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    $actWorkers = $this->game->board->getActiveWorkers();
    
    foreach($actWorkers as $worker1){
      foreach($aphWorkers as $worker2){
        if ($this->game->board->isNeighbour($worker1, $worker2))
        {
          $this->workerForcedIds[] = $worker1['id'];
          continue;
        }    
    }}
  }
  
  public function endOpponentTurn() { // TODO: name?
    $aphWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    $actWorkers = $this->game->board->getActiveWorkers();
    
    foreach($actWorkers as $worker1){
      if (!in_array($worker1['id'], $this->workerForcedIds))
        continue;
        
      foreach($aphWorkers as $worker2){
        $ok = false;
        if ($this->game->board->isNeighbour($worker1, $worker2))
          $ok = true;
      }
      
      if ($ok)
        continue;
      
      
      // The active player looses the game TODO: refactor
      
      // Notify
      $pId = self::getActivePlayerId();
      $args = [
        'i18n' => [],
        'playerName' => self::getActivePlayerName(),
      ];
      self::notifyAllPlayers('message', clienttranslate('${playerName} looses the game because it did not respect Aphrodite restrictions.'), $args);

      // 1v1 or 2v2 => end of the game
      if($this->playerManager->getPlayerCount() != 3){
        $player = $this->playerManager->getPlayer($pId);
        self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team != {$player->getTeam()}");
        $this->gamestate->nextState('endgame');
      }
      // 3 players => eliminate the player
      else {
        $this->playerManager->eliminate($pId);
        $this->gamestate->nextState('next');
      }
      
    }
    
  }

}
  
