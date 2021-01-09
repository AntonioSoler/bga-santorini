<?php

class Tartarus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TARTARUS;
    $this->name  = clienttranslate('Tartarus');
    $this->title = clienttranslate('God of the Abyss');
    $this->text  = [
      clienttranslate("[Setup:] Place your Workers first. After all players' Workers are placed, secretly place the Abyss Token on an unoccupied space. This space is the Abyss."),
      clienttranslate("[Lose Condition:] If any player's Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 0;

    $this->implemented = true;
  }

  /* * */

  public function getUiData()
  {
    $data = parent::getUiData();
    // Abyss not placed (must match 'all' value while secret)
    $data['counter'] = '??';
    if ($this->playerId != null) {
      $token = $this->getToken();
      $coords = $this->game->board->getMsgCoords($token);
      if ($token['location'] == 'board') {
        // Abyss location visible to all
        $data['counter'] = $coords;
      } else if ($token['location'] == 'secret') {
        // Abyss location visible to this player only
        $data['counter'] = [
          'all' => '??',
          $this->playerId => $coords,
        ];
      }
    }
    return $data;
  }

  public function argChooseFirstPlayer(&$arg)
  {
    // Tartarus must go first
    $arg['powers'] = [$this->id];
  }

  public function setup()
  {
    // Abyss is secret
    $this->getPlayer()->addToken('tokenAbyss');
    $this->updateUI();
  }

  public function getToken()
  {
    return $this->game->board->getPiecesByType('tokenAbyss')[0];
  }

  public function stateStartOfTurn()
  {
    // Place Abyss on the first turn
    $token = $this->getToken();
    return ($token['location'] == 'hand') ? 'power' : null;
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = false;

    // Abyss placement is not related to a specific worker (like Hydra)
    $empty = [
      'id' => 0,
      'playerId' => $this->playerId,
      'works' => $this->game->board->getAccessibleSpaces(),
    ];
    $arg['workers'] = [$empty];
  }

  public function usePower($action)
  {
    $token = $this->getToken();
    $space = $action[1];
    $this->placeToken($token, $space, 'secret');
    $this->updateUI();
  }

  public function stateAfterUsePower()
  {
    return 'move';
  }


  // principle: after a turn / possible win, check the first player to step on the abyss (this design allows players to restart)
  // this assumes that players do not move / force to a new space after a win, so does not work with the current implementation of Harpies
  // $loose parameter: throw announceLoose inside this function

  public function checkTurn($loose = true)
  {
    $loserId = null;
    $token = $this->getToken();
    $logs = $this->game->log->logsForCancelTurn();
    foreach (array_reverse($logs) as $log) {
      if ($log['piece_id'] != $token['id'] && ($log['action'] == 'move' || $log['action'] == 'force' || $log['action'] == 'placeWorker')) {
        $args = json_decode($log['action_arg'], true);
        if ($this->game->board->isSameSpace($args['to'], $token)) {
          $loserId = $this->game->board->getPiece($log['piece_id'])['player_id'];
          break;
        }
      }
    }

    if ($loserId && $loose) {
      $loser = $this->game->playerManager->getPlayer($loserId);
      $this->game->announceLose($this->game->msg['powerAbyss'], [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name2' => $loser->getName(),
        'coords' => $this->game->board->getMsgCoords($token),
      ], $loserId);
    }

    return $loserId;
  }

  public function endPlayerTurn()
  {
    $this->checkTurn();
  }

  public function endOpponentTurn()
  {
    $this->checkTurn();
  }

  // Happens when a player wins after stepping on the abyss: instead of announcing the defeat directly, we update the message
  public function checkWinning(&$arg)
  {
    if (!$arg['win']) {
      return;
    }

    $loserId = $this->checkTurn(false);
    if ($loserId != null) {
      $token = $this->getToken();
      $loser = $this->game->playerManager->getPlayer($loserId);
      $this->game->notifyAllPlayers('message', $this->game->msg['powerAbyss'], [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name2' => $loser->getName(),
        'coords' => $this->game->board->getMsgCoords($token),
      ]);
      $arg['win'] = true;
      $arg['pId'] = $this->game->playerManager->getOpponentsIds($loserId)[0];
    }
  }

  public function checkPlayerWinning(&$arg)
  {
    $this->checkWinning($arg);
  }

  public function checkOpponentWinning(&$arg)
  {
    $this->checkWinning($arg);
  }
}
