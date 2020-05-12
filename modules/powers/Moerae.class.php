<?php

class Moerae extends Power
{
  public static function getId() {
    return MOERAE;
  }

  public static function getName() {
    return clienttranslate('Moerae');
  }

  public static function getTitle() {
    return clienttranslate('Goddesses of Fate');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Take the Map, Shield, and Fate Token. Behind your Shield, secretly select a 2 X 2 square of Fate spaces by placing your Fate Token on the Map. When placing your Workers, place 3 of your color. "),
      clienttranslate("Win Condition: If an opponent Worker attempts to win by moving into one of your Fate spaces, you win instead.")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [HECATE, NEMESIS, TARTARUS];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  