<?php

abstract class SantoriniHeroPower extends SantoriniPower
{
  public function preEndPlayerTurn()
  {
    if ($this->game->log->getLastAction('usedPower') != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }
}
