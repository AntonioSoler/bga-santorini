<?php

class Prometheus extends Power
{
  public static function getId() {
    return PROMETHEUS;
  }

  public static function getName() {
    return clienttranslate('Prometheus');
  }

  public static function getTitle() {
    return clienttranslate('Titan Benefactor of Mankind');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: If your Worker does not move up, it may build both before and after moving.")
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
  