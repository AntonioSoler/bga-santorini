<?php

abstract class SantoriniHeroPower extends SantoriniPower
{
  public function getPlayerCount()
  {
    return [2];
  }

  public function isGoldenFleece()
  {
    return false;
  }
}
