<?php

class Maenads extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MAENADS;
    $this->name  = clienttranslate('Maenads');
    $this->title = clienttranslate('Raving Ones');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If your Workers neighbor an opponent's Worker on opposite sides, that opponent loses the game."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 41;
    $this->implemented = true;
  }

  /* * */

  public function endPlayerTurn()
  {
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId, true);
    foreach ($oppWorkers as $oppWorker) {
      foreach ($myWorkers as $myWorker) {
        if ($this->game->board->isNeighbour($myWorker, $oppWorker) && !is_null($this->game->board->getSpaceBehind($myWorker, $oppWorker, $myWorkers))) {
          $opponent = $this->game->playerManager->getPlayer($oppWorker['player_id']);

          // Hecate: do not win if the turn is illegal
          $powers = $opponent->getPowers();
          foreach ($powers as $power) {
            if ($power->getId() == HECATE && !$power->endOpponentTurn(true)) {
              return;
            }
          }

          $stats = [[$this->playerId, 'usePower']];
          $this->game->log->addAction('stats', $stats);

          // Eliminate opponent
          $this->game->announceLose(clienttranslate('${power_name}: ${player_name2} (${coords}) is neighbored on opposite sides by ${player_name} and is eliminated!'), [
            'i18n' => ['power_name'],
            'power_name' => $this->getName(),
            'player_name2' => $opponent->getName(),
            'coords' => $this->game->board->getMsgCoords($oppWorker),
          ], $opponent->getId());
          break;
        }
      }
    }
  }
}
