<?php

class Terpsichore extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = TERPSICHORE;
    $this->name  = clienttranslate('Terpsichore');
    $this->title = clienttranslate('Muse of Dancing');
    $this->text  = [
      clienttranslate("Your Turn: All of your Workers must move, and then all must build.")
    ];
    $this->players = [2, 3, 4];
    $this->banned  = [NEMESIS, HYPNUS, LIMUS, TARTARUS];
    $this->golden  = true;
  }

  /* * */

}
  