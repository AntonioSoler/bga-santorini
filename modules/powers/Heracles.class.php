<?php

class Heracles extends HeroPower
{
  public static function getId() {
    return HERACLES;
  }

  public static function getName() {
    return clienttranslate('Heracles');
  }

  public static function getTitle() {
    return clienttranslate('Doer of Great Deeds');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: Once, both your Workers build any number of domes (even zero) at any level.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  