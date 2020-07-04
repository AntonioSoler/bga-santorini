<?php

class Pegasus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PEGASUS;
    $this->name  = clienttranslate('Pegasus');
    $this->title = clienttranslate('Winged Horse');
    $this->text  = [
      clienttranslate("[Your Move:] Your Worker may move up more than one level, but cannot win the game by doing so."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 54;
  }

  /* * */
}
