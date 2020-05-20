<?php

class Artemis extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = ARTEMIS;
    $this->name  = clienttranslate('Artemis');
    $this->title = clienttranslate('Goddess of the Hunt');
    $this->text  = [
      clienttranslate("Your Move: Your Worker may move one additional time, but not back to its initial space.")
    ];
    $this->players = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
  public function argPlayerMove(&$arg)
  {
    $move = $this->game->log->getLastMove();
    // No move before => usual rule
    if($move == null)
      return;

    // Otherwise, let the player do a second move (not mandatory) with same worker
    $arg['skippable'] = true;
    Utils::filterWorkersById($arg, $move['pieceId']);
    Utils::filterWorks($arg, function($space, $worker) use ($move){
      // Not back to its initial space
      return !$this->game->board->isSameSpace($space, $move['from']);
    });
  }

  public function stateAfterMove()
  {
    return count($this->game->log->getLastMoves()) == 1? 'moveAgain' : null;
  }

}
