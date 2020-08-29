<?php

class Asteria extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ASTERIA;
    $this->name  = clienttranslate('Asteria');
    $this->title = clienttranslate('Goddess of Falling Stars');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If one of your Workers moved down this turn, you may build a dome in any unoccupied space."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 45;

    $this->implemented = true;
  }

  /* * */

  public function hasMovedDown()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function ($movedDown, $move) {
      return $movedDown || $move['to']['z'] < $move['from']['z'];
    }, false);
  }

  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild($this->playerId);
    // Normal build
    if ($build == null) {
      return null;
    }

    if ($this->hasMovedDown()) {
      $worker = $this->game->board->getPiece($build['pieceId']);
      $worker['works'] = $this->game->board->getAccessibleSpaces('build');
      Utils::updateWorkerArgsBuildDome($worker, false);
      $arg['workers'] = [$worker];
      $arg['skippable'] = true;
    }
  }

  public function stateAfterBuild()
  {
    $count = count($this->game->log->getLastBuilds($this->playerId));
    if ($count > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
    return $count == 1 && $this->hasMovedDown() ? 'build' : null;
  }
}
