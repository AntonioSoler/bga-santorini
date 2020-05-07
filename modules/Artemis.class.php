<?php

class Artemis extends Power
{
  public static function getId() {
    return ARTEMIS;
  }

  public static function getName() {
    return clienttranslate('Artemis');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Hunt');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Your Worker may move one additional time, but not back to its initial space.")
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
  