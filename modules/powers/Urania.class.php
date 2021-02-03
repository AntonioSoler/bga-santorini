<?php

class Urania extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = URANIA;
    $this->name  = clienttranslate('Urania');
    $this->title = clienttranslate('Muse of Astronomy');
    $this->text  = [
      clienttranslate("[Your Turn:] When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 49;

    $this->implemented = true;
  }

  /* * */

  public function playerMove($worker, $work)
  {
    // Normal neighbouring => classic move
    if ($this->game->board->isNeighbour($worker, $work, 'move')) {
      return false;
    }

    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addMove($worker, $work, $stats);
    $this->game->board->setPieceAt($worker, $work);

    return ['powerId' => URANIA, 'notifyOnly' => true];
  }
}
