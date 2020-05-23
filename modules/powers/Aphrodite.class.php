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
  }

  /* * */
}
