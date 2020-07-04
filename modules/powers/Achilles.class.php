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
    // TODO : check the state is "build" ?
    return is_null($this->game->log->getLastMove()) ? 'move' : null;
  }

  public function argPlayerMove(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if ($build == null) {
      return;
    }

    // Otherwise, the player has to move with the worker that built
    Utils::filterWorkersById($arg, $build['pieceId']);
  }

  public function preEndPlayerTurn()
  {
    if (count($this->game->log->getLastBuilds($this->playerId)) > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('usedPower', $stats);
    }
    parent::preEndPlayerTurn();
  }
}
