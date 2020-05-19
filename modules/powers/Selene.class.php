<?php

class Selene extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = SELENE;
    $this->name  = clienttranslate('Selene');
    $this->title = clienttranslate('Goddess of the Moon');
    $this->text  = [
      clienttranslate("Your Build: Instead of your normal build, your female Worker may build a dome at any level regardless of which Worker moved. ")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [GAEA];
    $this->golden  = true;
  }

  /* * */

}
  