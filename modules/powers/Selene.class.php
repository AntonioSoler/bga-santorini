<?php

class Selene extends SantoriniPower
{
  public static function getId() {
    return SELENE;
  }

  public static function getName() {
    return clienttranslate('Selene');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Moon');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Instead of your normal build, your female Worker may build a dome at any level regardless of which Worker moved. ")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [GAEA];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  