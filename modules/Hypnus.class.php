<?php

class Hypnus extends Power
{
  public static function getId() {
    return HYPNUS;
  }

  public static function getName() {
    return clienttranslate('Hypnus');
  }

  public static function getTitle() {
    return clienttranslate('God of Sleep');
  }

  public static function getText() {
    return [
      clienttranslate("Start of Opponent's Turn: If one of your opponent's Workers is higher than all of their others, it cannot move.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [TERPSICHORE];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  