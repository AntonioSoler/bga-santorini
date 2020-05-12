<?php

class Aeolus extends Power
{
  public static function getId() {
    return AEOLUS;
  }

  public static function getName() {
    return clienttranslate('Aeolus');
  }

  public static function getTitle() {
    return clienttranslate('God of the Winds');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place the Wind Token beside the board and orient it in any of the 8 directions to indicate which direction the Wind is blowing."),
      clienttranslate("End of Your Turn: Orient the Wind Token to any of the the eight directions."),
      clienttranslate("Any Move: Workers cannot move directly into the Wind.")
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
  