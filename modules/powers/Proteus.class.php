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
    return count($this->game->board->getPlacedWorkers($this->playerId)) > 1 ? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;
    
    $moves = $this->game->log->getLastMoves();
    $move = end($moves);
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
