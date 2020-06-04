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
      clienttranslate("Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers.")
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


  public function startOpponentTurn()
  {
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);
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


  public function getForcedWorkers()
  {
    $action = $this->game->log->getLastAction('forcedWorkers');
    if($action == null){
      return null;
    }
    return $action['workers'];
  }


  public function argOpponentMove(&$arg)
  {
    $forcedWorkers = $this->getForcedWorkers();
    if($forcedWorkers == null){
      return;
    }

    // Allow skip only if condition is satisfied
    if($arg['skippable']){
      foreach($arg['workers'] as $worker){
        $arg['skippable'] = $arg['skippable'] && ((!in_array($worker['id'], $forcedWorkers)) || $this->isNeighbouring($worker));
      }
    }


    // Last move => must be neighboring
    if($arg['mayMoveAgain'] === false){
      Utils::filterWorks($arg, function($space, $worker) use ($forcedWorkers){
        return (!in_array($worker['id'], $forcedWorkers)) || $this->isNeighbouring($space);
      });

      if(empty($arg['workers'])){
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: your last move must be to a space neighboring one of its Workers '), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
        ]);
      }
    }
    // Last move if not on perimeter => must be neighboring
    else if($arg['mayMoveAgain'] === 'perimeter'){
      Utils::filterWorks($arg, function($space, $worker) use ($forcedWorkers){
        return (!in_array($worker['id'], $forcedWorkers)) || $this->isNeighbouring($space) || $this->game->board->isPerimeter($space);
      });
    }

  }

  public function endOpponentTurn()
  {
    $forcedWorkers = $this->getForcedWorkers();
    if($forcedWorkers == null){
      return;
    }

    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);
    foreach($oppWorkers as $worker){
      if (!(in_array($worker['id'], $forcedWorkers)) || $this->isNeighbouring($worker)){
        continue;
      }

      $this->game->announceLose( clienttranslate('${player_name} looses the game because it did not respect Aphrodite restrictions.') );
    }
  }
}
