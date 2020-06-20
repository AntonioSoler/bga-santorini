<?php

class Artemis extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ARTEMIS;
    $this->name  = clienttranslate('Artemis');
    $this->title = clienttranslate('Goddess of the Hunt');
    $this->text  = [
      clienttranslate("[Your Move:] Your [Worker] may [move] one additional time, but not back to its initial space.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 53;

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
    Utils::filterWorks($arg, function ($space, $worker) use ($move) {
      // Not back to its initial space
      return !$this->game->board->isSameSpace($space, $move['from']);
    });
  }

  public function stateAfterMove()
  {
    $count = count($this->game->log->getLastMoves());
    if ($count > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
    return $count == 1 ? 'moveAgain' : null;
  }
}
