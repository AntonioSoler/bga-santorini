<?php

class Zeus extends Power
{
  public static function getId() {
    return ZEUS;
  }

  public static function getName() {
    return clienttranslate('Zeus');
  }

  public static function getTitle() {
    return clienttranslate('God of the Sky');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build a block under itself.")
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
  