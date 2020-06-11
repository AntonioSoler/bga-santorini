<?php

class Urania extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = URANIA;
    $this->name  = clienttranslate('Urania');
    $this->title = clienttranslate('Muse of Astronomy');
    $this->text  = [
      clienttranslate("[Your Turn:] When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 49;

    $this->implemented = true;
  }

  /* * */
  public function argPlayerWork(&$arg, $action)
  {
    $arg = $this->game->argPlayerWork($action, $this->game->board->getPlacedWorkers($this->playerId) , true);
  }

  public function argPlayerMove(&$arg)
  {
    $this->argPlayerWork($arg, 'move');
  }


  public function playerMove($worker, $work)
  {
    // Normal neighbouring => classic move
    if($this->game->board->isNeighbour($worker,$work, 'move')){
      return false;
    }

    // Otherwise, do a forced then a move
    $dx = abs($worker['x'] - $work['x']) <= 1? 0 : ($worker['x'] < $work['x']? 1 : -1);
    $dy = abs($worker['y'] - $work['y']) <= 1? 0 : ($worker['y'] < $work['y']? 1 : -1);
    $space = [
      'id' => $worker['id'],
      'x' => $worker['x'] + 5*$dx,
      'y' => $worker['y'] + 5*$dy,
      'z' => $worker['z'],
    ];

    $this->game->log->addForce($worker, $space);
    $this->game->log->addMove($space, $work);
    $this->game->board->setPieceAt($worker, $work);
    $this->game->playerMove($worker, $work, true);

    return true;
  }

  public function argPlayerBuild(&$arg)
  {
    $this->argPlayerWork($arg, 'build');

    $move = $this->game->log->getLastMove();
    if (!is_null($move)) {
      Utils::filterWorkersById($arg, $move['pieceId']);
    }
  }


}
