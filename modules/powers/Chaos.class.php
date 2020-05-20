<?php

class Chaos extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = CHAOS;
    $this->name  = clienttranslate('Chaos');
    $this->title = clienttranslate('Primordial Nothingness');
    $this->text  = [
      clienttranslate("Setup: Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck."),
      clienttranslate("Any Time: You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one.")
    ];
    $this->players = [2, 3, 4];
    
    $this->golden  = true;
  }

  /* * */

}
  