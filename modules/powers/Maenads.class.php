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
  public function isAround($myWorkers, $oppWorker)
  {
    foreach ($myWorkers as $worker) {
      if($this->game->board->isNeighbour($worker, $oppWorker) && !is_null($this->game->board->getSpaceBehind($worker, $oppWorker, $myWorkers))){
        return true;
      }
    }
    return false;
  }

  public function isAroundPlayer($myWorkers, $player)
  {
    foreach($this->game->board->getPlacedWorkers($player->getId()) as $oppWorker){
      if($this->isAround($myWorkers, $oppWorker)){
        return true;
      }
    }
    return false;
  }

  public function endPlayerTurn()
  {
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach($this->game->playerManager->getOpponents($this->playerId) as $opponent){
      if($this->isAroundPlayer($myWorkers, $opponent)){
        $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${opponent_name} loses because of ${player_name}'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'opponent_name' => $opponent->getName(),
          'player_name' => $this->getPlayer()->getName(),
        ]);
        $this->game->makeLoose($opponent->getId());
      }
    }
  }
}
