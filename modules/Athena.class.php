<?php

class Athena extends Power
{
  public static function getId() {
    return ATHENA;
  }

  public static function getName() {
    return clienttranslate('Athena');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Wisdom');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: If one of your Workers moved up on your last turn, opponent Workers cannot move up this turn.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  