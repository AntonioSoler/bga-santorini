<?php

class Circe extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CIRCE;
    $this->name  = clienttranslate('Circe');
    $this->title = clienttranslate('Divine Enchantress');
    $this->text  = [
      clienttranslate("[Start of Your Turn:] If an opponent's Workers do not neighbor each other, you alone have use of their power until your next turn."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 17;

    $this->implemented = true;
  }

  /* * */

  public function areTogether($workers)
  {
    $ok = count($workers) > 1;
    for ($i = 0; $ok && $i < count($workers); $i++) {
      for ($j = $i + 1; $ok && $j < count($workers); $j++) {
        $ok = $this->game->board->isNeighbour($workers[$i], $workers[$j], '');
      }
    }
    return $ok;
  }

  public function startPlayerTurn()
  {
    $opponent = $this->game->playerManager->getOpponents()[0]; // Only playable in 1v1
    $workers = $this->game->board->getPlacedWorkers($opponent->getId());
    if ($this->areTogether($workers)) {
      $action = $this->game->log->getLastAction('stealPower', null, 1);
      if ($action != null) {
        $myPowers = $this->getPlayer()->getPowers();
        if (count($myPowers) > 1) {
          // Return stolen powers
          $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name}\'s workers are neighboring'), [
            'i18n' => ['power_name'],
            'power_name' => $this->getName(),
            'player_name' => $opponent->getName(),
          ]);
          foreach ($myPowers as $power) {
            if ($power->getId() != $this->getId()) {
              $this->game->powerManager->movePower($power, $opponent, 'circe');
            }
          }
        }
      }
      return;
    }

    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addAction('stealPower', $stats, []);
    $opponentPowers = $opponent->getPowers();
    if (count($opponentPowers) > 0) {
      // Steal powers
      $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name}\'s workers are not neighboring'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $opponent->getName(),
      ]);
      foreach ($opponentPowers as $power) {
        $this->game->powerManager->movePower($power, $this->getPlayer(), 'circe');
        $power->startPlayerTurn();
      }
    }
  }
}
