<?php

class Europa extends SantoriniPower
{
  public static function getId() {
    return EUROPA;
  }

  public static function getName() {
    return clienttranslate('Europa & Talus');
  }

  public static function getTitle() {
    return clienttranslate('Queen & Guardian Automaton');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place the Talus Token on your God Power card."),
      clienttranslate("End of Your Turn: You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved."),
      clienttranslate("Any Time: All players treat the space containing the Talus Token as if it contains only a dome.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  