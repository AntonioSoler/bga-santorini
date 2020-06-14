<?php

class Eros extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = EROS;
    $this->name  = clienttranslate('Eros');
    $this->title = clienttranslate('God of Desire');
    $this->text  = [
      clienttranslate("[Setup:] Place your Workers anywhere along opposite edges of the board."),
      clienttranslate("[Win Condition:] You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game).")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 21;

    $this->implemented = true;
  }

  /* * */
  public function argPlayerPlaceWorker(&$arg)
  {
    // Only perimeter space
    Utils::filter($arg['accessibleSpaces'], function ($space) {
      return $this->game->board->isPerimeter($space);
    });

    // If no worker placed before, that's all
    $workers = $this->game->board->getPlacedActiveWorkers();
    if (count($workers) == 0)
      return;

    // Otherwise, the other worker should be on opposite edge
    $worker = $workers[0];
    Utils::filter($arg['accessibleSpaces'], function ($space) use ($worker) {
      return ($space['x'] == 0  && $worker['x'] == 4)
        || ($space['x'] == 4  && $worker['x'] == 0)
        || ($space['y'] == 0  && $worker['y'] == 4)
        || ($space['y'] == 4  && $worker['y'] == 0);
    });
  }


  public function checkPlayerWinning(&$arg)
  {
    if ($arg['win'])
      return;

    $move = $this->game->log->getLastWork();
    $workers = $this->game->board->getPlacedActiveWorkers();

    // Last work should be a move and Eros must have two workers left
    if ($move == null || $move['action'] != 'move' || count($workers) != 2) {
      return;
    }

    // The two workers must be adjacent and on same level
    if (!$this->game->board->isNeighbour($workers[0], $workers[1], 'move') || $workers[0]['z'] !=  $workers[1]['z']) {
      return;
    }

    // In a 2 or 4 player game, this level should be 1
    if ($this->game->playerManager->getPlayerCount() != 3 && $workers[0]['z'] != 1) {
      return;
    }

    // Eros wins
    $arg['win'] = true;
    $msg = clienttranslate('${power_name}: ${player_name} has neighboring workers on ${level_name}');
    $this->game->notifyAllPlayers('message', $msg, [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
      'level_name' => $this->game->levelNames[intval($workers[0]['z'])],
    ]);
  }
}
