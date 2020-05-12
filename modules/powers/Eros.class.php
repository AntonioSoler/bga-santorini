<?php

class Eros extends Power
{
  public static function getId() {
    return EROS;
  }

  public static function getName() {
    return clienttranslate('Eros');
  }

  public static function getTitle() {
    return clienttranslate('God of Desire');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place your Workers anywhere along opposite edges of the board."),
      clienttranslate("Win Condition: You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game).")
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
  