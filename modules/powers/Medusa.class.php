<?php

class Medusa extends Power
{
  public static function getId() {
    return MEDUSA;
  }

  public static function getName() {
    return clienttranslate('Medusa');
  }

  public static function getTitle() {
    return clienttranslate('Petrifying Gorgon');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  