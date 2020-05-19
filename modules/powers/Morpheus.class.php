<?php

class Morpheus extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = MORPHEUS;
    $this->name  = clienttranslate('Morpheus');
    $this->title = clienttranslate('God of Dreams');
    $this->text  = [
      clienttranslate("Start of Your Turn: Place a block or dome on your God Power card."),
      clienttranslate("Your Build: Your Worker cannot build as normal. Instead, your Worker may build any number of times (even zero) using blocks / domes collected on your God Power card. At any time, any player may exchange a block / dome on the God Power card for dome or a block of a different shape.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [];
    $this->golden  = false;
  }

  /* * */

}
  