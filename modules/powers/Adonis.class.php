<?php

class Adonis extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ADONIS;
    $this->name  = clienttranslate('Adonis');
    $this->title = clienttranslate('Devastatingly Handsome');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], choose one of your Workers and an opponent Worker. If possible, the Workers must be neighboring at the end of your opponent's next turn."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 25;

    $this->implemented = true;
  }

  /* * */

  /*
   * DISCLAIMER: This is a very basic version of Adonis!
   * Current implemenation must be banned against:
   * - Gods with multiple moves (Artemis, Hermes, Triton, etc.)
   * - Gods with complex moves (Charybdis, etc.)
   * - Gods with alternative turns (Siren, Jason, etc.)
   */

  public function stateAfterBuild()
  {
    // TODO: compute "if possible" during Adonis turn instead of opponent turn?
    // Would require bans against Gods with special moves (Apollo, Minotaur, etc.)
    return 'power';
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      foreach ($oppWorkers as $worker2) {
        $worker['works'][] = $this->game->board->getCoords($worker2);
      }
    }
  }

  public function usePower($action)
  {
    // Get info about the two workers
    $adonisWorker = $this->game->board->getPiece($action[0]);
    $oppWorker = $this->game->board->getPieceAt($action[1]);

    $this->game->log->addAction('usePowerAdonis', [], [
      'adonisWorkerId' => $adonisWorker['id'],
      'oppWorkerId' => $oppWorker['id'],
    ]);

    // Notify
    $oppPlayer = $this->game->playerManager->getPlayer($oppWorker['player_id']);
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: If possible, ${player_name2} (${coords2}) must end the next turn neighboring ${player_name} (${coords})'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(), // Adonis
      'player_name2' => $oppPlayer->getName(), // opponent
      'coords' => $this->game->board->getMsgCoords($adonisWorker),
      'coords2' => $this->game->board->getMsgCoords($oppWorker),
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

  /* * */

  public function getPowerData()
  {
    $powerData = $this->game->log->getLastAction('usePowerAdonis', $this->playerId);
    if ($powerData != null) {
      $powerData['adonisWorker'] = $this->game->board->getPiece($powerData['adonisWorkerId']);
      $powerData['oppWorker'] = $this->game->board->getPiece($powerData['oppWorkerId']);
    }
    return $powerData;
  }

  public function argOpponentMove(&$arg)
  {
    $powerData = $this->getPowerData();
    if ($powerData == null) {
      return;
    }

    if ($arg['mayMoveAgain'] !== false) {
      $oppPlayer = $this->game->playerManager->getPlayer();
      $oppPowerIds = implode(', ', $oppPlayer->getPowerIds());
      throw new BgaVisibleSystemException("argOpponentMove: Adonis is not supported (player: {$oppPlayer->getId}, power: $oppPowerIds)");
    }

    $test = ['workers' => $arg['workers']];
    Utils::filterWorkersById($test, $powerData['oppWorker']['id']);
    Utils::filterWorks($test, function ($space, $worker) use ($powerData) {
      return $this->game->board->isNeighbour($powerData['adonisWorker'], $space);
    });
    if (empty($test['workers'])) {
      // Notify
      $adonisPlayer = $this->game->playerManager->getPlayer($powerData['adonisWorker']['player_id']);
      $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: It is not possible for ${player_name2} (${coords2}) to end this turn neighboring ${player_name} (${coords})'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $adonisPlayer->getName(), // Adinois
        'player_name2' => $this->game->getActivePlayerName(), // opponent
        'coords' => $this->game->board->getMsgCoords($powerData['adonisWorker']),
        'coords2' => $this->game->board->getMsgCoords($powerData['oppWorker']),
      ]);
      // Discard immediately to prevent duplicate notifications
      $this->game->powerManager->removePower($this, 'hero');
    } else {
      // Allow skip only if condition is already satisfied
      $arg['workers'] = $test['workers'];
      if ($arg['skippable']) {
        $arg['skippable'] = $arg['skippable'] && $this->game->board->isNeighbour($powerData['adonisWorker'], $powerData['oppWorker']);
      }
    }
  }

  public function preEndOpponentTurn()
  {
    // Discard must happen after Adonis power affects the opponent
    if ($this->getPowerData() != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }
}
