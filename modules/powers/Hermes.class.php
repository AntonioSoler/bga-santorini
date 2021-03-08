<?php

class Hermes extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HERMES;
    $this->name  = clienttranslate('Hermes');
    $this->title = clienttranslate('God of Travel');
    $this->text  = [
      clienttranslate("[Your Turn:] If your Workers do not move up or down, they may each move any number of times (even zero), and then either builds."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 12;

    $this->implemented = true;
  }

  /* * */

  public function hasMovedUpOrDown()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function ($movedUp, $move) {
      return $movedUp || $move['to']['z'] != $move['from']['z'];
    }, false);
  }


  /* * */
  public function argPlayerMove(&$arg)
  {
    $arg['skippable'] = true;
    $arg['mayMoveAgain'] = HERMES;

    // No move before => usual rule
    $move = $this->game->log->getLastMove();
    if ($move == null) {
      return;
    }

    // Otherwise, let the player do a second move but on same height
    Utils::filterWorks($arg, function ($space, $worker) {
      return $space['z'] == $worker['z'];
    });
  }

  public function stateAfterMove()
  {
    return $this->hasMovedUpOrDown() ? null : 'move';
  }


  public function argPlayerBuild(&$arg)
  {
    // Moved up/down => usual rule
    if ($this->hasMovedUpOrDown()) {
      return;
    }

    // Otherwise, let the player build with any worker
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
    }
  }

  public function endPlayerTurn()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    $build = $this->game->log->getLastBuild($this->playerId);
    if (count($moves) != 1 || $build['pieceId'] != $moves[0]['pieceId']) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
