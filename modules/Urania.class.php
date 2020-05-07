<?php

class Urania extends Power
{
  public static function getId() {
    return URANIA;
  }

  public static function getName() {
    return clienttranslate('Urania');
  }

  public static function getTitle() {
    return clienttranslate('Muse of Astronomy');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [APHRODITE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  