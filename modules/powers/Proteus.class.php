<?php

class Proteus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PROTEUS;
    $this->name  = clienttranslate('Proteus');
    $this->title = clienttranslate('Shapeshifting Sea God');
    $this->text  = [
      clienttranslate("[Setup:] When placing your Workers, place 3 of your color."),
      clienttranslate("[Your Move:] After your Worker moves, if possible, force one of your other Workers into the space just vacated."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 30;

    $this->implemented = true;
  }

  /* * */

  public function setup()
  {
    $this->getPlayer()->addWorker('m');
  }

  public function stateAfterMove()
  {
    if (count($this->game->board->getPlacedWorkers($this->playerId)) > 1) {
      // Verify the space is actually empty (e.g., Charybdis may force Proteus back)
      $move = $this->game->log->getLastMove();
      $acc = $this->game->board->getAccessibleSpaces('build');
      Utils::filter($acc, function ($space) use ($move) {
        return ($space['x'] == $move['from']['x'] && $space['y'] == $move['from']['y']);
      });
      if (count($acc) > 0) {
        return 'power';
      }
    }
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;

    $move = $this->game->log->getLastMove();
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorkersById($workers, $move['pieceId'], false);
    foreach ($workers as &$worker) {
      $worker['works'] = [$move['from']];
    }
    $arg['workers'] = $workers;
  }

  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $worker = $this->game->board->getPiece($wId);

    // Force worker
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Notify force
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $worker,
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->getActivePlayerName(),
      'level_name' => $this->game->levelNames[intval($space['z'])],
      'coords' => $this->game->board->getMsgCoords($worker, $space),
    ]);
  }

  public function stateAfterUsePower()
  {
    return 'build';
  }
}
