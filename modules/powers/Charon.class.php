<?php

class Charon extends Power
{
  public static function getId() {
    return CHARON;
  }

  public static function getName() {
    return clienttranslate('Charon');
  }

  public static function getTitle() {
    return clienttranslate('Ferryman to the Underworld');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HECATE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  