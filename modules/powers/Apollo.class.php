<?php

class Apollo extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = APOLLO;
    $this->name  = clienttranslate('Apollo');
    $this->title = clienttranslate('God Of Music');
    $this->text  = [
      clienttranslate("Your Move: Your Worker may move into an opponent Worker's space by forcing their Worker to the space yours just vacated.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function argPlayerMove(&$arg)
  {
    $allWorkers = $this->game->board->getPlacedWorkers();
    foreach($arg["workers"] as &$worker)
    foreach($allWorkers as $worker2){
      if($worker['player_id'] == $worker2['player_id'])
        continue;

      if($this->game->board->isNeighbour($worker, $worker2, 'move'))
        $worker['works'][] = ['x' => $worker2['x'], 'y' => $worker2['y'], 'z' => $worker2['z']];
    }
  }

  public function playerMove($worker, $work)
  {
    // If space is free, we can do a classic move -> return false
    $worker2 = self::getObjectFromDB( "SELECT * FROM piece WHERE x = {$work['x']} AND y = {$work['y']} AND z = {$work['z']}");
    if ($worker2 == null)
      return false;

    // Switch workers
    self::DbQuery( "UPDATE piece SET x = {$worker2['x']}, y = {$worker2['y']}, z = {$worker2['z']} WHERE id = {$worker['id']}" );
    self::DbQuery( "UPDATE piece SET x = {$worker['x']}, y = {$worker['y']}, z = {$worker['z']} WHERE id = {$worker2['id']}" );
    $this->game->log->addMove($worker, $worker2);
    $this->game->log->addForce($worker2, $worker);

    // Notify
    $args = [
      'i18n' => ['power_name'],
      'piece1' => $worker,
      'piece2' => $worker2,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
    ];
    $this->game->notifyAllPlayers('workerSwitched', clienttranslate('${power_name}: ${player_name} forces a swap with ${player_name2}'), $args);

    return true;
  }
}
