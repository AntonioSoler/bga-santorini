<?php

class Bellerophon extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = BELLEROPHON;
    $this->name  = clienttranslate('Bellerophon');
    $this->title = clienttranslate('Tamer of Pegasus');
    $this->text  = [
      clienttranslate("[Your Move:] [Once], your Worker moves up two levels."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 14;

    $this->implemented = true;
  }

  /* * */
  
  
  public function argPlayerMove(&$arg)
  {
    $arg['ifPossiblePower'] = BELLEROPHON;
  }

  public function playerMove($worker, $work)
  {
    if ($work['z'] - $worker['z'] == 2) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('usedPower', $stats);
    }
    return false;
  }
}
