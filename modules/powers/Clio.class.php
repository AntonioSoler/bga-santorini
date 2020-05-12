<?php

class Clio extends Power
{
  public static function getId() {
    return CLIO;
  }

  public static function getName() {
    return clienttranslate('Clio');
  }

  public static function getTitle() {
    return clienttranslate('Muse of History');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Place a Coin Token on each of the first 3 blocks your Workers build."),
      clienttranslate("Opponent's Turn: Opponents treat spaces containing your Coin Tokens as if they contain only a dome.")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [CIRCE, NEMESIS];
  }

  public static function isGoldenFleece() {
    return false; 
  }

  /* * */

}
  