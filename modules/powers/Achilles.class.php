<?php

class Achilles extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ACHILLES;
    $this->name  = clienttranslate('Achilles');
    $this->title = clienttranslate('Volatile Warrior');
    $this->text  = [
      clienttranslate("[Your Turn:] [Once], your Worker builds both before and after moving."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 10;

    $this->implemented = true;
  }

  /* * */

  public function stateStartOfTurn()
  {
    return 'build';
  }

  public function argPlayerBuild(&$arg)
  {
    $arg['ifPossiblePower'] = ACHILLES;
    $build = $this->game->log->getLastBuild();
    $move  = $this->game->log->getLastMove();
    // Already built or move before => usual rule
    if ($build != null || $move != null) {
      return;
    }

    $arg['skippable'] = true;
  }

  public function stateAfterBuild()
  {
    return is_null($this->game->log->getLastMove()) ? 'move' : null;
  }

  public function stateAfterSkip()
  {
    return is_null($this->game->log->getLastMove()) ? 'move' : null;
  }

  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = ACHILLES;
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if ($build == null) {
      return;
    }

    // Otherwise, the player has to move with the worker that built
    Utils::filterWorkersById($arg, $build['pieceId']);
  }

  public function playerBuild($worker,$work)
  {
    if (count($this->game->log->getLastMoves($this->playerId)) == 0) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('usedPower', $stats);
    }
    return false;
  }
}
