<?php

class Circe extends Power
{
  public static function getId() {
    return CIRCE;
  }

  public static function getName() {
    return clienttranslate('Circe');
  }

  public static function getTitle() {
    return clienttranslate('Divine Enchantress');
  }

  public static function getText() {
    return [
      clienttranslate("Start of Your Turn: If an opponent's Workers do not neighbor each other, you alone have use of their power until your next turn.")
    ];
  }

  public static function getPlayers() {
    return [2];
  }

  public static function getBannedIds() {
    return [CLIO, HECATE];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  