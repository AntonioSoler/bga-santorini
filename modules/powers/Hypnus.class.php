<?php

class Hypnus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HYPNUS;
    $this->name  = clienttranslate('Hypnus');
    $this->title = clienttranslate('God of Sleep');
    $this->text  = [
      clienttranslate("Start of Opponent's Turn: If one of your opponent's Workers is higher than all of their others, it cannot move.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 23;

    $this->implemented = true;
  }

  /* * */
  protected static function cmpZ($worker1, $worker2){
    if ($worker1['z'] == $worker2['z'])
      return 0;
    return ($worker1['z'] > $worker2['z']) ? -1 : 1;
  }

  public function startOpponentTurn()
  {
    // If at least two workers remeaining
    foreach($this->game->playerManager->getOpponents() as $opponent){
      $workers = $this->game->board->getPlacedWorkers($opponent->getId());
      if (count($workers) < 2){
        continue;
      }

      // Sort them by height and see if first one is strictly higher
      usort($workers, 'self::cmpZ');
      if(self::cmpZ($workers[0], $workers[1]) != 0){
        $this->game->log->addAction('blockedWorker', ['wId' => $workers[0]['id'] ]);
      }
    }
  }

  public function argOpponentMove(&$arg)
  {
    $action = $this->game->log->getLastAction('blockedWorker');
    if($action != null){
      Utils::filterWorkersById($arg, $action['wId'], false);
    }
  }
}
