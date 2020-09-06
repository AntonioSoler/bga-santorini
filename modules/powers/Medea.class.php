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

    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    $allWorkers = $arg['workers'];
    // Must use getPlacedOpponentWorkers() so Medea cannot target Clio's invisible workers
    foreach ($this->game->board->getPlacedOpponentWorkers() as $oppWorker) {
      $allWorkers[] = $oppWorker;
    }

    foreach ($arg['workers'] as &$worker) {
      foreach ($allWorkers as $worker2) {
        if ($worker['id'] != $worker2['id'] && $worker2['z'] > 0 && $this->game->board->isNeighbour($worker, $worker2)) {
          $worker['works'][] = SantoriniBoard::getCoords($worker2, 0, true);
        }
      }
    }
  }

  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $worker = $this->game->board->getPiece($space['id']);

    // Remove the piece(s) under the worker at this x,y
    $pieces = $this->game->board->getBlocksAt($space);
    foreach ($pieces as $piece) {
      $this->removePiece($piece);
    }

    // Force the worker to ground level
    $space['z'] = 0;
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
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
