<?php

class Atalanta extends HeroPower
{
  public static function getId() {
    return ATALANTA;
  }

  public static function getName() {
    return clienttranslate('Atalanta');
  }

  public static function getTitle() {
    return clienttranslate('Swift Huntress');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Once, your Worker moves any number of additional times.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  