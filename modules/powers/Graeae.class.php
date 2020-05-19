<?php

class Graeae extends SantoriniPower
{
  public static function getId() {
    return GRAEAE;
  }

  public static function getName() {
    return clienttranslate('Graeae');
  }

  public static function getTitle() {
    return clienttranslate('The Gray Hags');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: When placing your Workers, place 3 of your color."),
      clienttranslate("Your Build: You choose which Worker of yours builds.")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [NEMESIS];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  