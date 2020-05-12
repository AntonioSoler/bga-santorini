<?php

class Poseidon extends Power
{
  public static function getId() {
    return POSEIDON;
  }

  public static function getName() {
    return clienttranslate('Poseidon');
  }

  public static function getTitle() {
    return clienttranslate('God of the Sea');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: If your unmoved Worker is on the ground level, it may build up to three times.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  