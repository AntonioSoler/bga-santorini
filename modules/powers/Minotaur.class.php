<?php

class Minotaur extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MINOTAUR;
    $this->name  = clienttranslate('Minotaur');
    $this->title = clienttranslate('Bull-headed Monster');
    $this->text  = [
      clienttranslate("Your Move: Your Worker may move into an opponent Worker's space, if their Worker can be forced one space straight backwards to an unoccupied space at any level.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function argPlayerMove(&$arg)
  {
    $allWorkers = $this->game->board->getPlacedWorkers();
    $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');

    foreach ($arg["workers"] as &$worker) {
      foreach ($allWorkers as $worker2) {
        if ($worker['player_id'] == $worker2['player_id']) {
          continue;
        }

        // Must be accessible
        if (!$this->game->board->isNeighbour($worker, $worker2, 'move')) {
          continue;
        }

        // Must be a free space behind
        $space = $this->game->board->getSpaceBehind($worker, $worker2, $accessibleSpaces);
        if (!is_null($space)) {
          $worker['works'][] = ['x' => $worker2['x'], 'y' => $worker2['y'], 'z' => $worker2['z']];
        }
      }
    }
  }

  public function playerMove($worker, $work)
  {
    // If space is occupied, first do a force
    $worker2 = self::getObjectFromDB("SELECT * FROM piece WHERE x = {$work['x']} AND y = {$work['y']} AND z = {$work['z']}");
    if ($worker2 != null) {
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      $space = $this->game->board->getSpaceBehind($worker, $worker2, $accessibleSpaces);
      if (is_null($space)) {
        throw new BgaVisibleSystemException("Minotaur: No available space behind opponent worker");
      }
      self::DbQuery("UPDATE piece SET x = {$space['x']}, y = {$space['y']}, z = {$space['z']} WHERE id = {$worker2['id']}");
      $this->game->log->addForce($worker2, $space);

      // Notify (same text as Charon to help translators)
      $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name}'), [
        'i18n' => ['power_name', 'level_name'],
        'piece' => $worker2,
        'space' => $space,
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
        'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
        'level_name' => $this->game->levelNames[intval($space['z'])],
      ]);
    }

    // Always do a classic move
    return false;
  }
}
