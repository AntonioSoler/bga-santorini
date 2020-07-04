<?php

class Hippolyta extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HIPPOLYTA;
    $this->name  = clienttranslate('Hippolyta');
    $this->title = clienttranslate('Queen of the Amazons');
    $this->text  = [
      clienttranslate("[Any Time:] All Workers except your female Worker may only move diagonally."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 47;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerMove(&$arg)
  {
    $femaleWorkers = $this->game->board->getPlacedActiveWorkers('f');
    Utils::filterWorksUnlessMine($arg, $femaleWorkers, array($this->game->board, 'isDiagonal'));
  }

  public function argTeammateMove(&$arg)
  {
    $this->argPlayerMove($arg);
  }

  public function argOpponentMove(&$arg)
  {
    Utils::filterWorks($arg, array($this->game->board, 'isDiagonal'));
  }

  public function endPlayerTurn()
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addAction('stats', $stats);
  }

  public function endTeammateTurn()
  {
    $this->endPlayerTurn();
  }

  public function endOpponentTurn()
  {
    $this->endPlayerTurn();
  }
}
