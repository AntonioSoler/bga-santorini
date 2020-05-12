<?php

class Minotaur extends Power
{
  public static function getId() {
    return MINOTAUR;
  }

  public static function getName() {
    return clienttranslate('Minotaur');
  }

  public static function getTitle() {
    return clienttranslate('Bull-headed Monster');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Your Worker may move into an opponent Worker's space, if their Worker can be forced one space straight backwards to an unoccupied space at any level.")
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
  