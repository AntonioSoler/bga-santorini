<?php

class Nemesis extends SantoriniPower
{
  public static function getId() {
    return NEMESIS;
  }

  public static function getName() {
    return clienttranslate('Nemesis');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Retribution');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: If none of an opponent's Workers neighbor yours, you may force as many of your opponent's Workers as possible to take the spaces you occupy, and vice versa.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [CLIO, GAEA, GRAEAE, MOERAE, APHRODITE, BIA, MEDUSA, TERPSICHORE, THESEUS];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  