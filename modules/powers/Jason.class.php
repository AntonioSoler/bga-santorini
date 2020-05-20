<?php

class Jason extends SantoriniHeroPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = JASON;
    $this->name  = clienttranslate('Jason');
    $this->title = clienttranslate('Leader of the Argonauts');
    $this->text  = [
      clienttranslate("Setup: Take one extra Worker of your color. This is kept on your God Power card until needed."),
      clienttranslate("Your Turn: Once, instead of your normal turn, place your extra Worker on an unoccupied ground-level perimeter space. This Worker then builds.")
    ];
    $this->players = [2];
    $this->golden  = false;
  }

  /* * */

}
