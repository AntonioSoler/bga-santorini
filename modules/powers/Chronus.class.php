<?php

class Chronus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHRONUS;
    $this->name  = clienttranslate('Chronus');
    $this->title = clienttranslate('God of Time');
    $this->text  = [
      clienttranslate("[Win Condition:] You also win when there are at least five Complete Towers on the board.")
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 2;

    $this->implemented = true;
  }

  /* * */

  protected function checkWinning(&$arg)
  {
    if ($arg['win']) {
      return;
    }

    $count = $this->game->board->getCompleteTowerCount();
    if ($count < 5) {
      return;
    }

    // Chronus wins
    $arg['win'] = true;
    $arg['winStats'] = [[$this->playerId, 'usePower']];
    $arg['pId'] = $this->playerId;
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${count} Complete Towers are on the board'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'count' => $count,
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
