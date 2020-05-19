<?php

class Theseus extends SantoriniHeroPower
{
  public static function getId() {
    return THESEUS;
  }

  public static function getName() {
    return clienttranslate('Theseus');
  }

  public static function getTitle() {
    return clienttranslate('Slayer of the Minotaur');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: Once, if any of your Workers is exactly 2 levels below any neighboring opponent Workers, remove one of those opponent Workers from play.")
    ];
  }

  public static function getBannedIds() {
    return [NEMESIS];
  }

  /* * */

}
  