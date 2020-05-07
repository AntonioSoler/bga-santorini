<?php

class Demeter extends Power
{
  public static function getId() {
    return DEMETER;
  }

  public static function getName() {
    return clienttranslate('Demeter');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Harvest');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional time, but not on the same space.")
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
  