<?php

class Medusa extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MEDUSA;
    $this->name  = clienttranslate('Medusa');
    $this->title = clienttranslate('Petrifying Gorgon');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 22;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    // If no build before => usual rule
    $build = $this->game->log->getLastBuild();
    if ($build == null) {
      return;
    }

    // Otherwise, we check if a kill is possible
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        if ($this->game->board->isNeighbour($worker, $worker2, 'build') && $worker2['z'] < $worker['z']) {
          $worker['works'][] = SantoriniBoard::getCoords($worker2, 1);
        }
      }
    }
  }

  public function endPlayerTurn()
  {
    // Check if any kill is possible (using argPlayerBuild)
    $arg = $this->game->argPlayerBuild();
    if (count($arg['workers']) == 0) {
      return;
    }

    foreach ($arg['workers'] as $worker) {
      foreach ($worker['works'] as $work) {
        $worker2 = self::getObjectFromDB("SELECT * FROM piece WHERE location = 'board' AND x = {$work['x']} AND y = {$work['y']} AND z = {$work['z']}");
        if ($worker2 == null) {
          // Might happens if the only worker already killed the piece just before
          continue;
        }

        $this->game->playerKill($worker2, $this->getName());
        $this->game->playerBuild($worker, SantoriniBoard::getCoords($worker2, 2));
      }
    }
  }
}
