<?php

class Triton extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TRITON;
    $this->name  = clienttranslate('Triton');
    $this->title = clienttranslate('God of the Waves');
    $this->text  = [
      clienttranslate("[Your Move:] Each time your Worker moves into a perimeter space, it may immediately move again."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 52;

    $this->implemented = true;
  }

  /* * */

  public function hasMovedOnPerimeter()
  {
    $move = $this->game->log->getLastMove($this->playerId);
    return $this->game->board->isPerimeter($move['to']);
  }


  public function argPlayerMove(&$arg)
  {
    $arg['mayMoveAgain'] = TRITON;
    // No move before => usual rule
    $moves = $this->game->log->getLastMoves();
    if (count($moves) == 0) {
      return;
    }

    Utils::filterWorkersById($arg, $moves[0]['pieceId']);
    $arg['skippable'] = true;
  }

  public function stateAfterMove()
  {
    return $this->hasMovedOnPerimeter() ?  'move' : null;
  }

  public function endPlayerTurn()
  {
    $value = count($this->game->log->getLastMoves()) - 1;
    if ($value > 0) {
      $stats = [[$this->playerId, 'usePower', $value]];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
