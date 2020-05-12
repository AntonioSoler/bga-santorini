<?php

class Dionysus extends Power
{
  public static function getId() {
    return DIONYSUS;
  }

  public static function getName() {
    return clienttranslate('Dionysus');
  }

  public static function getTitle() {
    return clienttranslate('God of Wine');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Each time a Worker you control creates a Complete Tower, you may take an additional turn using an opponent Worker instead of your own. No player can win during these additional turns.")
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
  