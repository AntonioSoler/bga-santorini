<?php

class Castor extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CASTOR;
    $this->name  = clienttranslate('Castor & Pollux');
    $this->title = clienttranslate('Divine & Mortal Twins');
    $this->text  = [
      clienttranslate("[Alternative Turn:] Move with all of your Workers. Do not build. Alternative Turn: Do not move. Build with all of your Workers.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 13;
  }

  /* * */
}
