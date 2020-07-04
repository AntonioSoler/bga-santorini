<?php

class Atalanta extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ATALANTA;
    $this->name  = clienttranslate('Atalanta');
    $this->title = clienttranslate('Swift Huntress');
    $this->text  = [
      clienttranslate("[Your Move:] [Once], your Worker moves any number of additional times."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 28;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $move = $this->game->log->getLastMove();
    // No move before => usual rule
    if ($move == null) {
      $arg['mayMoveAgain'] = true;
      return;
    }

    // Otherwise, let the player do a second move (not mandatory) with same worker
    $arg['skippable'] = true;
    Utils::filterWorkersById($arg, $move['pieceId']);
  }

  public function stateAfterMove()
  {
    return 'moveAgain';
  }

  public function preEndPlayerTurn()
  {
    if (count($this->game->log->getLastMoves($this->playerId)) > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('usedPower', $stats);
    }
    parent::preEndPlayerTurn();
  }
}
