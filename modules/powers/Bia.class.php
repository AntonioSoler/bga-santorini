<?php

class Bia extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = BIA;
    $this->name  = clienttranslate('Bia');
    $this->title = clienttranslate('Goddess of Violence');
    $this->text  = [
      clienttranslate("[Setup:] Place your Workers first. Your workers must be placed in perimeter spaces."),
      clienttranslate("[Your Move:] If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 0;

    $this->implemented = true;
  }

  /* * */

  public function argChooseFirstPlayer(&$arg)
  {
    // Bia must go first
    $arg['powers'] = [$this->id];
  }

  public function argPlayerPlaceWorker(&$arg)
  {
    Utils::filter($arg['accessibleSpaces'], function ($space) {
      return $this->game->board->isPerimeter($space);
    });
  }
  
  
  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = BIA;
  }


  public function afterPlayerMove($worker, $work)
  {
    $x = 2 * $work['x'] - $worker['x'];
    $y = 2 * $work['y'] - $worker['y'];

    // Must use getPlacedOpponentWorkers() so Bia cannot target Clio's invisible workers
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers(null, true);
    foreach ($oppWorkers as &$oppWorker) {
      if ($oppWorker['x'] == $x and $oppWorker['y'] == $y) {
        $this->game->playerKill($oppWorker, $this->getName());
        break;
      }
    }
  }
}
