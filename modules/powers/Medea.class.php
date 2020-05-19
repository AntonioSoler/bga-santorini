<?php

class Medea extends SantoriniHeroPower
{
  public static function getId() {
    return MEDEA;
  }

  public static function getName() {
    return clienttranslate('Medea');
  }

  public static function getTitle() {
    return clienttranslate('Powerful Sorceress');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: Once, remove one block from under any number of Workers neighboring your unmoved Worker. You also remove any Tokens on the blocks.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  