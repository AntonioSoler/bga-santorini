<?php

class Hermes extends Power
{
  public static function getId() {
    return HERMES;
  }

  public static function getName() {
    return clienttranslate('Hermes');
  }

  public static function getTitle() {
    return clienttranslate('God of Travel');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: If your Workers do not move up or down, they may each move any number of times (even zero), and then either builds.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HARPIES];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  