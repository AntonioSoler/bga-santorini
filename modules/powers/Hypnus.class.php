<?php

class Hypnus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HYPNUS;
    $this->name  = clienttranslate('Hypnus');
    $this->title = clienttranslate('God of Sleep');
    $this->text  = [
      clienttranslate("[Start of Opponent's Turn:] If one of your opponent's Workers is higher than all of their others, it cannot move."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 23;

    $this->implemented = true;
  }

  /* * */
  protected static function cmpZ($worker1, $worker2)
  {
    if ($worker1['z'] == $worker2['z'])
      return 0;
    return ($worker1['z'] > $worker2['z']) ? -1 : 1;
  }

  public function startOpponentTurn()
  {
    // If at least one worker remeaining
    $workers = $this->game->board->getPlacedWorkers($this->game->getActivePlayerId(), true);
    if (count($workers) < 1) {
      return;
    }

    // Sort them by height and see if first one is strictly higher. A single worker is always blocked
    usort($workers, 'self::cmpZ');
    if (count($workers) == 1 || self::cmpZ($workers[0], $workers[1]) != 0) {
      $this->game->notifyWithSecret($workers[0], 'message', clienttranslate('${power_name}: ${player_name} (${coords}) cannot move this turn'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(), // opponent
        'coords' => $this->game->board->getMsgCoords($workers[0]),
      ]);
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('blockedWorker', $stats, ['wId' => $workers[0]['id']]);
    }
  }

  public function argOpponentMove(&$arg)
  {
    $action = $this->game->log->getLastAction('blockedWorker');
    if ($action != null) {
      Utils::filterWorkersById($arg, $action['wId'], false);
    }
  }
}
