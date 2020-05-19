<?php

class Morpheus extends SantoriniPower
{
  public static function getId() {
    return MORPHEUS;
  }

  public static function getName() {
    return clienttranslate('Morpheus');
  }

  public static function getTitle() {
    return clienttranslate('God of Dreams');
  }

  public static function getText() {
    return [
      clienttranslate("Start of Your Turn: Place a block or dome on your God Power card."),
      clienttranslate("Your Build: Your Worker cannot build as normal. Instead, your Worker may build any number of times (even zero) using blocks / domes collected on your God Power card. At any time, any player may exchange a block / dome on the God Power card for dome or a block of a different shape.")
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
  