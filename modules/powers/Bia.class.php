<?php

class Bia extends Power
{
  public static function getId() {
    return BIA;
  }

  public static function getName() {
    return clienttranslate('Bia');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Violence');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place your Workers first."),
      clienttranslate("Your Move: If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS, TARTARUS];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  