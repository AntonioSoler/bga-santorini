<?php

class Achilles extends SantoriniHeroPower
{
  public static function getId() {
    return ACHILLES;
  }

  public static function getName() {
    return clienttranslate('Achilles');
  }

  public static function getTitle() {
    return clienttranslate('Volatile Warrior');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: Once, your Worker builds both before and after moving.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  