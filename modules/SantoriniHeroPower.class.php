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

  public function preEndPlayerTurn()
  {
    if ($this->game->log->getLastAction('heroPower') != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }
}
