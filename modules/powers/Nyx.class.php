<?php

class Nyx extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = NYX;
    $this->name  = clienttranslate('Nyx');
    $this->title = clienttranslate('Goddess of Night');
    $this->text  = [
      clienttranslate("[Setup:] Before players choose powers, the first player selects a God Power card to be Nyx's Night Power."),
      clienttranslate("[End of All Turns:] If there are an odd number of Complete Towers in play, gain your Night Power and your opponent loses their God Power. If there are an even number of Complete Towers, lose your Night Power and your opponent gains their God Power."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 51;
  }

  /* * */
}
