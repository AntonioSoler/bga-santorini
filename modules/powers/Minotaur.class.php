<?php

class Minotaur extends Power
{
  public static function getId() {
    return MINOTAUR;
  }

  public static function getName() {
    return clienttranslate('Minotaur');
  }

  public static function getTitle() {
    return clienttranslate('Bull-headed Monster');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Your Worker may move into an opponent Worker's space, if their Worker can be forced one space straight backwards to an unoccupied space at any level.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */
  public function getSpaceBehind(&$worker, &$worker2, &$accessibleSpaces)
  {
    $x = 2*$worker2['x'] - $worker['x'];
    $y = 2*$worker2['y'] - $worker['y'];
    $spaces = array_values(array_filter($accessibleSpaces, function($space) use ($x,$y){
      return $space['x'] == $x && $space['y'] == $y;
    }));

    return (count($spaces) == 1)? $spaces[0] : null;
  }

  public function argPlayerMove(&$arg)
  {
    $allWorkers = $this->game->board->getPlacedWorkers();
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');

    foreach($arg["workers"] as &$worker)
    foreach($allWorkers as $worker2){
      if($worker['player_id'] == $worker2['player_id'])
        continue;

      // Must be accessible
      if(!$this->game->board->isNeighbour($worker, $worker2, 'move'))
        continue;

      // Must be a free space behind
      $space = $this->getSpaceBehind($worker,$worker2, $accessibleSpaces);
      if(!is_null($space))
        $worker['works'][] = ['x' => $worker2['x'], 'y' => $worker2['y'], 'z' => $worker2['z']];
    }
  }

  public function playerMove($worker, $work)
  {
    // If space is free, we can do a classic move -> return false
    $worker2 = self::getObjectFromDB( "SELECT * FROM piece WHERE x = {$work['x']} AND y = {$work['y']} AND z = {$work['z']}");
    if ($worker2 == null)
      return false;

    // Push worker
    self::DbQuery( "UPDATE piece SET x = {$worker2['x']}, y = {$worker2['y']}, z = {$worker2['z']} WHERE id = {$worker['id']}" );
    $this->game->log->addMove($worker, $worker2);

    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
    $space = $this->getSpaceBehind($worker,$worker2,$accessibleSpaces);
    self::DbQuery( "UPDATE piece SET x = {$space['x']}, y = {$space['y']}, z = {$space['z']} WHERE id = {$worker2['id']}" );
    $this->game->log->addForce($worker2, $space);

    // Notify
    $args = [
      'i18n' => [],
      'piece1' => $worker,
      'piece2' => $worker2,
      'space'  => $space,
      'playerName' => $this->game->getActivePlayerName(),
    ];
    $this->game->notifyAllPlayers('workerPushed', clienttranslate('${playerName} pushed opponent\'s worker with its worker'), $args);

    $this->game->gamestate->nextState('moved');

    return true;
  }

}
