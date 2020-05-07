<?php

class Persephone extends Power
{
  public static function getId() {
    return PERSEPHONE;
  }

  public static function getName() {
    return clienttranslate('Persephone');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Spring Growth');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: If possible, at least one Worker must move up this turn.")
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
  