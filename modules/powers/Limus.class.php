<?php

class Limus extends Power
{
  public static function getId() {
    return LIMUS;
  }

  public static function getName() {
    return clienttranslate('Limus');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Famine');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: Opponent Workers cannot build on spaces neighboring your Workers, unless building a dome to create a Complete Tower.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [TERPSICHORE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  