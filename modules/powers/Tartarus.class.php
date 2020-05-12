<?php

class Tartarus extends Power
{
  public static function getId() {
    return TARTARUS;
  }

  public static function getName() {
    return clienttranslate('Tartarus');
  }

  public static function getTitle() {
    return clienttranslate('God of the Abyss');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Take the Map, Shield, and one Abyss Token. Place your Workers first. After all players' Workers are placed, hide the Map behind the Shield and secretly place your Abyss Token on an unoccupied space. This space is the Abyss."),
      clienttranslate("Lose Condition: If any player's Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss.")
    ];
  }

  public static function getPlayers() {
    return [2];
  }

  public static function getBannedIds() {
    return [BIA, HECATE, MOERAE, TERPSICHORE];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  