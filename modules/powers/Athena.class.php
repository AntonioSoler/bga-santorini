<?php

class Athena extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ATHENA;
    $this->name  = clienttranslate('Athena');
    $this->title = clienttranslate('Goddess of Wisdom');
    $this->text  = [
      clienttranslate("[Opponent's Turn:] If one of your Workers moved up on your last turn, opponent Workers cannot move up this turn.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 63;

    $this->implemented = true;
  }

  /* * */

  public function hasMovedUp()
  {
    // Handle vs Circe and Chaos
    $action = self::getObjectFromDb("SELECT * FROM log WHERE (`action` = 'move' AND `player_id` = {$this->playerId}) OR (`action` IN  ('stealPower', 'returnPower', 'powerChanged')) ORDER BY log_id DESC LIMIT 1");
    if ($action != null && $action['action'] != 'move') {
      return false;
    }

    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function ($movedUp, $move) {
      return $movedUp || $move['to']['z'] > $move['from']['z'];
    }, false);
  }

  public function afterPlayerMove($worker, $work)
  {
    if ($this->hasMovedUp()) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
  }

  public function argOpponentMove(&$arg)
  {
    if (!$this->hasMovedUp()) {
      return;
    }

    // Useful against Dionysos
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorksUnlessMine($arg, $myWorkers, function ($space, $worker) {
      return $space['z'] <= $worker['z'];
    });
  }
}
