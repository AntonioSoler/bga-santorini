<?php

class Bellerophon extends HeroPower
{
  public static function getId() {
    return BELLEROPHON;
  }

  public static function getName() {
    return clienttranslate('Bellerophon');
  }

  public static function getTitle() {
    return clienttranslate('Tamer of Pegasus');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Once, your Worker moves up two levels.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  