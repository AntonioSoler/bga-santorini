<?php

class Terpsichore extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TERPSICHORE;
    $this->name  = clienttranslate('Terpsichore');
    $this->title = clienttranslate('Muse of Dancing');
    $this->text  = [
      clienttranslate("[Your Turn:] All of your Workers must move, and then all must build."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 33;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = TERPSICHORE;
    $moves = $this->game->log->getLastMoves();
    
    $workers = count($this->game->board->getPlacedActiveWorkers());
    if ($workers == 0)
      $arg['skippable'] = true;
      
    // No move before => usual rule
    if (count($moves) == 0) {
      return;
    }

    // Otherwise, the unmoved workers can move
    $workersIds = array_map(function ($move) {
      return $move['pieceId'];
    }, $moves);
    Utils::filterWorkersById($arg, $workersIds, false);
  }

  public function stateAfterMove()
  {
    return count($this->game->log->getLastMoves()) < count($this->game->board->getPlacedActiveWorkers()) ? 'move' : null;
  }


  public function argPlayerBuild(&$arg)
  {
    $workers = count($this->game->board->getPlacedActiveWorkers());
      
    $builds = $this->game->log->getLastBuilds();
    $workersIds = array_map(function ($build) {
      return $build['pieceId'];
    }, $builds);

    $arg = $this->game->argPlayerWork('build');
    Utils::filterWorkersById($arg, $workersIds, false);
    if ($workers == 0)
      $arg['skippable'] = true;
  }

  public function stateAfterBuild()
  {
    return count($this->game->log->getLastBuilds()) < count($this->game->board->getPlacedActiveWorkers()) ? 'build' : null;
  }

  public function endPlayerTurn()
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addAction('stats', $stats);
  }
}
