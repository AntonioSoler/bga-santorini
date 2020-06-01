<?php

class Aphrodite extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = APHRODITE;
    $this->name  = clienttranslate('Aphrodite');
    $this->title = clienttranslate('Goddess of Love');
    $this->text  = [
      clienttranslate("Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers. Currently, if you do not respect the condition, you loose.")
    ];
    $this->playerCount = [2, 4];
    $this->golden  = false;
    $this->orderAid = 57;

    $this->implemented = true;
  }

  /* * */
  public function isNeighbouring($oppWorker){
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach($workers as $worker){
      if ($this->game->board->isNeighbour($worker, $oppWorker, '')){
        return true;
      }
    }

    return false;
  }


  public function startOpponentTurn() {
    $oppWorkers = $this->game->board->getPlacedActiveWorkers();
    $forcedWorkers = [];
    foreach($oppWorkers as $worker){
      if($this->isNeighbouring($worker)){
        $forcedWorkers[] = $worker['id'];
      }
    }

    if(!empty($forcedWorkers)){
      $this->game->log->addAction('forcedWorkers', ['workers' => $forcedWorkers ]);
    }
  }


  public function endOpponentTurn() {
    $action = $this->game->log->getLastAction('forcedWorkers');
    if($action == null){
      return;
    }

    $forcedWorkers = $action['workers'];
    $oppWorkers = $this->game->board->getPlacedActiveWorkers();
    foreach($oppWorkers as $worker){
      if (!(in_array($worker['id'], $forcedWorkers)) || $this->isNeighbouring($worker)){
        continue;
      }

      $this->game->announceLose( clienttranslate('${player_name} looses the game because it did not respect Aphrodite restrictions.') );
    }
  }
}
