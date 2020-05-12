<?php

abstract class HeroPower extends Power {
  public static function getPlayers() {
    return [2];
  }

  public static function isGoldenFleece() {
    return false; 
  }
}
