<?php

abstract class SantoriniHeroPower extends SantoriniPower
{
  public function getPlayers()
  {
    return [2];
  }

  public function isGoldenFleece()
  {
    return false;
  }
}
