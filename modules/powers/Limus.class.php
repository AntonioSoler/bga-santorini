<?php

class Limus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = LIMUS;
    $this->name  = clienttranslate('Limus');
    $this->title = clienttranslate('Goddess of Famine');
    $this->text  = [
      clienttranslate("Opponent's Turn: Opponent Workers cannot build on spaces neighboring your Workers, unless building a dome."),
      clienttranslate("[REVISED POWER]")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 16;

    $this->implemented = true;
  }

  /* * */
  public function argOpponentBuild(&$arg)
  {
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach ($myWorkers as &$worker) {
      Utils::filterWorks($arg, function (&$space, $oppworker) use ($worker) {
        // can build only a dome or at a non-neighbouring space
        if (!$this->game->board->isNeighbour($space, $worker, 'build')) {
          return true;
        }
        if (!in_array(3, $space['arg'])) {
          return false;
        }
        $space['arg'] = [3];
        return true;
      });
    }
  }
}
