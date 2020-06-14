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
      clienttranslate("[Setup:] Before players choose powers, the Challenger publicly selects a God Power card with the Golden Fleece icon to be Nyx’s Night Power, which will start the game not in play."),
      clienttranslate("[Start of all Turns:] If there are an even number of [Complete Towers] in play, lose your Night Power if you have it, and your opponent gains their God Power if they do not. If there are an odd number of [Complete Towers] in play, your opponent loses their God Power if they have it, and you gain your Night Power if you do not."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 51;
  }

  /* * */
}
