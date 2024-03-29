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
      clienttranslate("[End of Your Turn:] If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 22;

    $this->implemented = true;
  }

  /* * */

  
  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = MEDUSA;
  }

  // Optional parameter used by Hecate
  public function argPlayerBuild(&$arg, $lookSecret = false)
  {
    // If no build before => usual rule
    $build = $this->game->log->getLastBuild();
    if ($build == null) {
      return;
    }

    // Otherwise, we check if a kill is possible
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers(null, $lookSecret);
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = [];
      foreach ($oppWorkers as $worker2) {
        if ($this->game->board->isNeighbour($worker, $worker2, 'build') && $worker2['z'] < $worker['z']) {
          $worker['works'][] = SantoriniBoard::getCoords($worker2, 1, true);
        }
      }
    }
  }

  // Optional parameter used by Hecate
  public function endPlayerTurn($arg = null)
  {
    // Check if any kill is possible (using argPlayerBuild)
    if ($arg == null) {
      $arg = $this->game->argPlayerBuild();
    }
    if (count($arg['workers']) == 0) {
      return;
    }

    foreach ($arg['workers'] as $worker) {
      foreach ($worker['works'] as $work) {
        $worker2 = $this->game->board->getPiece($work['id']);
        if ($worker2 != null && ($worker2['location'] == 'board' || $worker2['location'] == 'secret')) {
          $this->game->playerKill($worker2, $this->getName(), true, true);
          $this->game->playerBuild($worker, SantoriniBoard::getCoords($worker2, 2));
        }
      }
    }
  }
}
