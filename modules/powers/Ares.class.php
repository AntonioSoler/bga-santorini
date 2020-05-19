<?php

class Ares extends SantoriniPower
{
  public static function getId() {
    return ARES;
  }

  public static function getName() {
    return clienttranslate('Ares');
  }

  public static function getTitle() {
    return clienttranslate('God of War');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  