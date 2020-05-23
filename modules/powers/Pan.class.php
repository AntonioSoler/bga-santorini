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
      clienttranslate("Win Condition: You also win if your Worker moves down two or more levels.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */

  public function checkPlayerWinning(&$arg)
  {
    if ($arg['win']) {
      return;
    }

    $move = $this->game->log->getLastWork();
    if ($move == null || $move['action'] != 'move' || $move['to']['z'] > $move['from']['z'] - 2) {
      return;
    }

    // Pan wins
    $arg['win'] = true;
    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} moved down two or more levels'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
    ]);
  }
}
