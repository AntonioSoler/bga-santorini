<?php

class Hydra extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HYDRA;
    $this->name  = clienttranslate('Hydra');
    $this->title = clienttranslate('Many-Headed Monster');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If none of your Workers neighbor each other, gain a new Worker and place it in one of the lowest unoccupied spaces next to the Worker you moved. Otherwise, remove one of your Workers from play."),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 43;
  }

  /* * */
}
