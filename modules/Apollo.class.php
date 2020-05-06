<?php

class Apollo extends Power
{
  public static $id     = APOLLO;
  public static $name   = 'Apollo';
  public static $title  = 'God Of Music';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Move: Your Worker may move into an opponent Worker's space by forcing their Worker to the space yours just vacated."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

  public static function checkDistances($a, $b){
    return abs($a['x'] - $b['x']) <= 1 && abs($a['y'] - $b['y']) <= 1 && $b['z'] <= $a['z'] + 1;
  }

  public function argPlayerMove(&$workers)
  {
    $allWorkers = $this->game->getPlacedWorkers();
    foreach($workers as &$worker)
    foreach($allWorkers as $worker2){
      if($worker['player_id'] == $worker2['player_id'])
        continue;

      if(self::checkDistances($worker, $worker2))
        $worker['accessibleSpaces'][] = ['x' => $worker2['x'], 'y' => $worker2['y'], 'z' => $worker2['z']];
    }
  }


  public function playerMove($wId, $x, $y, $z)
  {
    // If space is free, we can do a classic move -> return false
    $worker2 = self::getObjectFromDB( "SELECT * FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z'" );
    if ($worker2 == null)
      return false;

    // Get information about the piece
    $worker = $this->game->getPiece($wId);

    // Check if it's belong to active player
    if ($worker['player_id'] != $this->game->getActivePlayerId())
      throw new BgaUserException( _("This worker is not yours") );

    // Check if worker can move to this space
    $space = [  'x' => $x, 'y' => $y, 'z' => $z ];
    if (!self::checkDistances($worker, $space))
      throw new BgaUserException( _("You cannot reach this space with this worker") );

    // Switch workers
    self::DbQuery( "UPDATE piece SET x = '$x', y = '$y', z = '$z' WHERE id = '$wId'" );
    self::DbQuery( "UPDATE piece SET x = '".$worker['x']."', y = '".$worker['y']."', z = '".$worker['z']."' WHERE id = '".$worker2['id']."'" );

    // Set moved worker
    $this->game->setGamestateValue( 'movedWorker', $wId );

    // Notify
    $args = [
      'i18n' => [],
      'piece1' => $worker,
      'piece2' => $worker2,
      'playerName' => $this->game->getActivePlayerName(),
    ];
    $this->game->notifyAllPlayers('workerSwitched', clienttranslate('${playerName} switch his worker with opponent\'s worker'), $args);

    $this->game->gamestate->nextState('moved');

    return true;
  }

}
