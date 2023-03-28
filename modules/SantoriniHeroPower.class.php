<?php

abstract class SantoriniHeroPower extends SantoriniPower
{
  public function preEndPlayerTurn()
  {
    // do nothing when switching power to Gaea
    if ($this->game->getGameStateValue('switchState') > 0)
      return;
      
    if ($this->game->log->getLastAction('usedPower') != null) {
      $this->game->powerManager->removePower($this, 'hero');
    }
  }
}
