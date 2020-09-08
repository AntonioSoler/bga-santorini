<?php

class Nyx extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = NYX;
    $this->name  = clienttranslate('Nyx');
    $this->title = clienttranslate('Goddess of Night');
    $this->text  = [
      clienttranslate("[Setup:] Before players choose powers, the first player selects a God Power card to be Nyx's Night Power."),
      clienttranslate("[End of All Turns:] If there are an odd number of Complete Towers in play, gain your Night Power and your opponent loses their God Power. If there are an even number of Complete Towers, lose your Night Power and your opponent gains their God Power."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 51;

    $this->implemented = true;
  }

  /* * */

  public function hasNightPower()
  {
    $powerId = $this->game->powerManager->getSpecialPowerId('nyxNight');
    return $this->game->powerManager->hasPower($powerId, $this->playerId);
  }

  public function endPlayerTurn()
  {
    $count = $this->game->board->getCompleteTowerCount();
    $hasNightPower = $this->hasNightPower();
    if (!$hasNightPower && $count % 2 == 1) {
      // Odd: Add night power
      $this->game->notifyAllPlayers('message', $this->game->msg['powerCompleteTowers'], [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'count' => $count,
      ]);
      $opponent = $this->game->playerManager->getOpponents($this->playerId)[0];
      $opponentPowers = $opponent->getPowers();
      foreach ($opponentPowers as $oppPower) {
        $this->game->powerManager->removePower($oppPower, 'nyx');
      }
      $nightPower = $this->game->powerManager->getSpecialPower('nyxNight', $this->playerId);
      $this->game->powerManager->addPower($nightPower, 'nyxNight');
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    } else if ($hasNightPower && $count % 2 == 0) {
      // Even: Remove night power
      $this->game->notifyAllPlayers('message', $this->game->msg['powerCompleteTowers'], [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'count' => $count,
      ]);
      $opponent = $this->game->playerManager->getOpponents($this->playerId)[0];
      $opponentPowers = $this->game->powerManager->getPowersInLocation('nyx');
      foreach ($opponentPowers as $oppPower) {
        $oppPower->playerId = $opponent->getId();
        $this->game->powerManager->addPower($oppPower, 'nyx');
      }
      $nightPower = $this->game->powerManager->getSpecialPower('nyxNight', $this->playerId);
      $this->game->powerManager->removePower($nightPower, 'nyxNight');
    }
  }

  public function endOpponentTurn()
  {
    $this->endPlayerTurn();
  }
}
