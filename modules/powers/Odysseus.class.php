<?php

class Odysseus extends HeroPower
{
  public static function getId() {
    return ODYSSEUS;
  }

  public static function getName() {
    return clienttranslate('Odysseus');
  }

  public static function getTitle() {
    return clienttranslate('Cunning Leader');
  }

  public static function getText() {
    return [
      clienttranslate("Start of Your Turn: Once, force to unoccupied corner spaces any number of opponent Workers that neighbor your Workers.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  