<?php

class Hestia extends Power
{
  public static function getId() {
    return HESTIA;
  }

  public static function getName() {
    return clienttranslate('Hestia');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Hearth and Home');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional time, but this cannot be on a perimeter space.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  