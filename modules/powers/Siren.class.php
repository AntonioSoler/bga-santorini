<?php

class Siren extends Power
{
  public static function getId() {
    return SIREN;
  }

  public static function getName() {
    return clienttranslate('Siren');
  }

  public static function getTitle() {
    return clienttranslate('Alluring Sea Nymph');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place the Arrow Token beside the board and orient it in any of the 8 directions to indicate the direction of the Siren's Song."),
      clienttranslate("Your Turn: You may choose not to take your normal turn. Instead, force one or more opponent Workers one space in the direction of the Siren's Song to unoccupied spaces at any level.")
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
  