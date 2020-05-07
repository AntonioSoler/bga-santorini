<?php

class Chronus extends Power
{
  public static function getId() {
    return CHRONUS;
  }

  public static function getName() {
    return clienttranslate('Chronus');
  }

  public static function getTitle() {
    return clienttranslate('God of Time');
  }

  public static function getText() {
    return [
      clienttranslate("Win Condition: You also win when there are at least five Complete Towers on the board.")
    ];
  }

  public static function getPlayers() {
    return [2];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  