<?php

class Triton extends Power
{
  public static function getId() {
    return TRITON;
  }

  public static function getName() {
    return clienttranslate('Triton');
  }

  public static function getTitle() {
    return clienttranslate('God of the Waves');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Each time your Worker moves into a perimeter space, it may immediately move again.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HARPIES];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  