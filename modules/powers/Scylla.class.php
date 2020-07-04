<?php

class Scylla extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = SCYLLA;
    $this->name  = clienttranslate('Scylla');
    $this->title = clienttranslate('Menacing Sea Monster');
    $this->text  = [
      clienttranslate("[Your Move:] If your Worker moves from a space that neighbors an opponent's Worker, you may force their Worker into the space yours just vacated."),
    ];
    $this->playerCount = [2, 4];
    $this->golden  = true;
    $this->orderAid = 36;
  }

  /* * */
}
