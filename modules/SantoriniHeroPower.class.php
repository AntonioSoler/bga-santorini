<?php

abstract class SantoriniHeroPower extends SantoriniPower {
  public static function getPlayers() {
    return [2];
  }

  public static function isGoldenFleece() {
    return false; 
  }
}
