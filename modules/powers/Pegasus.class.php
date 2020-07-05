<?php

class Pegasus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PEGASUS;
    $this->name  = clienttranslate('Pegasus');
    $this->title = clienttranslate('Winged Horse');
    $this->text  = [
      clienttranslate("[Your Move:] Your Worker may move up more than one level, but cannot win the game by doing so."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 54;

    $this->implemented = true;
  }

  /* * */

  public function afterPlayerMove($worker, $work)
  {
    $move = $this->game->log->getLastMove($this->playerId);
    if ($move['to']['z'] - $move['from']['z'] > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('usedPower', $stats);
    }
  }

  public function checkPlayerWinning(&$arg)
  {
    if ($arg['win'] && $this->game->log->getLastAction('usedPower') != null) {
      // Stop the win
      $arg['win'] = false;
      unset($arg['winStats']);
      $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} cannot win by moving up more than one level'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
      ]);
    }
  }
}
