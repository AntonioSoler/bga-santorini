<?php

class Circe extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CIRCE;
    $this->name  = clienttranslate('Circe');
    $this->title = clienttranslate('Divine Enchantress');
    $this->text  = [
      clienttranslate("Start of Your Turn: If an opponent's Workers do not neighbor each other, you alone have use of their power until your next turn.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */
}
