<?php

class Hades extends Power
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return HADES;
  }

  public static function getName() {
    return clienttranslate('Hades');
  }

  public static function getTitle() {
    return clienttranslate('God of the Underworld');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: Opponent Workers cannot move down.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [PAN];
  }

  public static function isGoldenFleece() {
    return true;
  }


  public function argOpponentMove(&$arg)
  {
    Utils::filterWorks($arg, function($space, $worker){
        return $space['z'] >= $worker['z'];
    });
  }

}
