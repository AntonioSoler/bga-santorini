<?php

class Hecate extends SantoriniPower
{
  public static function getId() {
    return HECATE;
  }

  public static function getName() {
    return clienttranslate('Hecate');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Magic');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Take the Map, Shield, and 2 Worker Tokens. Hide the Map behind the Shield and secretly place your Worker Tokens on the Map to represent the location of your Workers on the game board. Place your Workers last."),
      clienttranslate("Your Turn: Move a Worker Token on the Map as if it were on the game board. Build on the game board, as normal."),
      clienttranslate("Any Time: If an opponent attempts an action that would not be legal due to the presence of your secret Workers, their action is cancelled and they lose the rest of their turn. When possible, use their power on their behalf to make their turns legal without informing them.")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [CHARON, CIRCE, MOERAE, TARTARUS];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  