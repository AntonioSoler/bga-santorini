<?php

class Gaea extends SantoriniPower
{
  public static function getId() {
    return GAEA;
  }

  public static function getName() {
    return clienttranslate('Gaea');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Earth');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Take 2 extra Workers of your color. These are kept on your God Power card until needed."),
      clienttranslate("Any Build: When a Worker builds a dome, Gaea may immediately place a Worker from her God Power card onto a ground-level space neighboring the dome.")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [ATLAS, NEMESIS, SELENE];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  