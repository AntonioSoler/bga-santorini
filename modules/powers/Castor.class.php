<?php

class Castor extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CASTOR;
    $this->name  = clienttranslate('Castor & Pollux');
    $this->title = clienttranslate('Divine & Mortal Twins');
    $this->text  = [
      clienttranslate("[Alternative Turn:] Move with all of your Workers. Do not build."),
      clienttranslate("[Alternative Turn:] Do not move. Build with all of your Workers."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 13;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $moves = $this->game->log->getLastMoves();
    if (count($moves) > 1)
    {
      $lastMove = $this->game->log->getLastMove();
      if ($this->game->board->isSameSpace($lastMove['from'], $lastMove['to'])) // dummy move from Charybdis
      	$moves = [$moves[1]];
    }
    // Allow usual turn or skip
    if (count($moves) <= 1) {
      $arg['skippable'] = true;
    }

    // Otherwise, every worker must move once
    $workersIds = array_map(function ($move) {
      return $move['pieceId'];
    }, $moves);
    Utils::filterWorkersById($arg, $workersIds, false);
  }

  public function stateAfterMove()
  {
    $moves = count($this->game->log->getLastMoves());
    $lastMove = $this->game->log->getLastMove();
    if ($this->game->board->isSameSpace($lastMove['from'], $lastMove['to'])) // dummy move from Charybdis
    	$moves = $moves - 1;
    $workers = count($this->game->board->getPlacedActiveWorkers());
    if ($moves == 0 || $workers == 1) {
      return null;
    } else if ($moves < $workers) {
      return 'move';
    } else {
      return 'endturn';
    }
  }

  public function argPlayerBuild(&$arg)
  {
    $moves = $this->game->log->getLastMoves();
    // Normal turn
    if (count($moves) == 1) {
      return;
    }

    // Oeverwise, every workers must build once
    $builds = $this->game->log->getLastBuilds();
    $workersIds = array_map(function ($build) {
      return $build['pieceId'];
    }, $builds);

    $arg = $this->game->argPlayerWork('build');
    Utils::filterWorkersById($arg, $workersIds, false);
  }

  public function stateAfterBuild()
  {
    $moves = count($this->game->log->getLastMoves());
    // Normal turn
    if ($moves == 1) {
      return null;
    }

    $builds = count($this->game->log->getLastBuilds());
    $workers = count($this->game->board->getPlacedActiveWorkers());
    return $builds < $workers ? 'build' : null;
  }

  public function endPlayerTurn()
  {
    $moves = count($this->game->log->getLastMoves());
    if ($moves != 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
