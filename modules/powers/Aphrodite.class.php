<?php

class Aphrodite extends SantoriniPower
{
  public static function getId() {
    return APHRODITE;
  }

  public static function getName() {
    return clienttranslate('Aphrodite');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Love');
  }

  public static function getText() {
    return [
      clienttranslate("Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers.")
    ];
  }

  public static function getPlayers() {
    return [2, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS, URANIA];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  