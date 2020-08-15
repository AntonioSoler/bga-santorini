<?php

class Medea extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MEDEA;
    $this->name  = clienttranslate('Medea');
    $this->title = clienttranslate('Powerful Sorceress');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], remove all blocks from under a Worker neighboring either of your Workers. You also remove any Tokens on the blocks."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 19;

    $this->implemented = true;
  }

  /* * */

  public function stateAfterBuild()
  {
    $arg = [];
    $this->argUsePower($arg);
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : null;
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $allWorkers = $this->game->board->getPlacedWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      foreach ($allWorkers as $worker2) {
        if ($worker['id'] != $worker2['id'] && $worker2['z'] > 0 && $this->game->board->isNeighbour($worker, $worker2)) {
          $worker['works'][] = $this->game->board->getCoords($worker2);
        }
      }
    }
  }

  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $destroyCount = $space['z'] - 1;
    $worker = $this->game->board->getPieceAt($space);

    while ($space['z'] > 0) {
      $space['z']--;

      // Remove piece
      $piece = $this->game->board->getPieceAt($space);
      self::DbQuery("UPDATE piece SET location = 'box' WHERE id = {$piece['id']}");
      $this->game->log->addRemoval($piece);

      // Notify (same text as Ares to help translators)
      $this->game->notifyAllPlayers('pieceRemoved', clienttranslate('${power_name}: ${player_name} removes a block (${coords})'), [
        'i18n' => ['power_name'],
        'piece' => $piece,
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
        'coords' => $this->game->board->getMsgCoords($piece),
      ]);
    }

    // Force the worker to ground level
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Notify (same text as Charon to help translators)
    $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name} (${coords})'), [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker,
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker['player_id'])->getName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($worker),
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
