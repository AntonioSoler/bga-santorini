<?php

class Hephaestus extends Power
{
  public static function getId() {
    return HEPHAESTUS;
  }

  public static function getName() {
    return clienttranslate('Hephaestus');
  }

  public static function getTitle() {
    return clienttranslate('God of Blacksmiths');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional block (not dome) on top of your first block.")
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
  