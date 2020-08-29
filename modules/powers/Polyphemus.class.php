<?php

class Polyphemus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = POLYPHEMUS;
    $this->name  = clienttranslate('Polyphemus');
    $this->title = clienttranslate('Gigantic Cyclops');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], your Worker builds up to 2 domes at any level on any unoccupied spaces on the board."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 61;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    $builds = $this->game->log->getLastBuilds();
    // No build before => usual rule
    if (count($builds) == 0) {
      return;
    }

    // Otherwise, let the player do 2 more builds anywhere (not mandatory)
    $arg['skippable'] = true;
    $worker = $this->game->board->getPiece($builds[0]['pieceId']);
    $worker['works'] = $this->game->board->getAccessibleSpaces('build');
    Utils::updateWorkerArgsBuildDome($worker, false);
    $arg['workers'] = [$worker];
  }

  public function stateAfterBuild()
  {
    $count = count($this->game->log->getLastBuilds($this->playerId));
    return $count < 3 ? 'build' : null;
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
