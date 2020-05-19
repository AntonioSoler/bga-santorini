<?php

class Terpsichore extends SantoriniPower
{
  public static function getId() {
    return TERPSICHORE;
  }

  public static function getName() {
    return clienttranslate('Terpsichore');
  }

  public static function getTitle() {
    return clienttranslate('Muse of Dancing');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: All of your Workers must move, and then all must build.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS, HYPNUS, LIMUS, TARTARUS];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  