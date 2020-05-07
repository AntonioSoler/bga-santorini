<?php

class Polyphemus extends HeroPower
{
  public static function getId() {
    return POLYPHEMUS;
  }

  public static function getName() {
    return clienttranslate('Polyphemus');
  }

  public static function getTitle() {
    return clienttranslate('Gigantic Cyclops');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: Once, your Worker builds up to 2 domes at any level on any unoccupied spaces on the board.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  