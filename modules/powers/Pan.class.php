<?php

class Pan extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PAN;
    $this->name  = clienttranslate('Pan');
    $this->title = clienttranslate('God of the Wild');
    $this->text  = [
      clienttranslate("[Win Condition:] You also win if your Worker moves down two or more levels."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 38;

    $this->implemented = true;
  }

  /* * */

  public function checkWinning(&$arg)
  {
    if ($arg['win']) {
      return;
    }

    $move = $this->game->log->getLastWinableWork();
    if ($move == null || $move['action'] != 'move' || $move['to']['z'] > $move['from']['z'] - 2) {
      return;
    }

    // Pan wins during opponent turn if Eris moved this worker (but not Dionysus)
    $piece = $this->game->board->getPiece($move['pieceId']);
    if ($piece['player_team'] != $this->getPlayer()->getTeam() || $this->game->log->isAdditionalTurn(DIONYSUS)) {
      return;
    }

    // Pan wins
    $arg['win'] = true;
    $arg['winStats'] = [[$this->playerId, 'usePower']];
    $arg['pId'] = $this->playerId;
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} moved down two or more levels'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
    ]);
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
