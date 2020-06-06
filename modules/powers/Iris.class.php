<?php

class Iris extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = IRIS;
    $this->name  = clienttranslate('Iris');
    $this->title = clienttranslate('Goddess of the Rainbow');
    $this->text  = [
      clienttranslate("[Your Move:] If there is a Worker neighboring your Worker and the space directly on the other side of it is unoccupied, your Worker may move to that space regardless of its level.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 37;
  }

  /* * */
}
