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
      clienttranslate("Your Move: Each time your Worker moves into a perimeter space, it may immediately move again.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;

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
    // No move before => usual rule
    $move = $this->game->log->getLastMove();
    if ($move == null) {
      return;
    }

    Utils::filterWorkersById($arg, $move['pieceId']);
    $arg['skippable'] = true;
  }

  public function stateAfterMove()
  {
    return $this->hasMovedOnPerimeter() ?  'moveAgain' : null;
  }
}
