<?php

class Harpies extends Power
{
  public static function getId() {
    return HARPIES;
  }

  public static function getName() {
    return clienttranslate('Harpies');
  }

  public static function getTitle() {
    return clienttranslate('Winged Menaces');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: Each time an opponent's Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HERMES, TRITON];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  