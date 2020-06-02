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
      clienttranslate("Start of Your Turn: If an opponent's Workers do not neighbor each other, you alone have use of their power until your next turn.")
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
    for($i = 0; $ok && $i < count($workers); $i++){
      for($j = $i + 1; $ok && $j < count($workers); $j++){
        $ok = $this->game->board->isNeighbour($workers[$i], $workers[$j], '');
      }
    }
    return $ok;
  }


  public function notify($steal, $playerId, $power){
    $msg = $steal ? clienttranslate('${power_name}: ${player_name} steals ${player_name2}\'s power : ${power_name2}')
                  : clienttranslate('${power_name}: ${player_name} returns ${player_name2}\'s power : ${power_name2}');

    $this->game->notifyAllPlayers('powersChanged', $msg, [
      'i18n' => ['power_name', 'power_name2'],
      'power_name' => $this->getName(),
      'power_name2' => $power->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($playerId)->getName(),
      'fplayers' => $this->game->playerManager->getUiData(),
    ]);
  }


  public function startPlayerTurn(){
    $opponent = $this->game->playerManager->getOpponents()[0]; // Only playable in 1v1
    $workers = $this->game->board->getPlacedWorkers($opponent->getId());
    if($this->areTogether($workers)){
      // A power was stealed at previous turn => return it
      $action = $this->game->log->getLastAction('stealPower', null, 1);
      if($action != null){
        foreach($this->game->playerManager->getPlayer()->getPowers() as $power){
          if($power->getId() != $this->getId()){
            $this->game->cards->moveCard($power->getId(), 'hand', $action['playerId']);
            $this->notify(false, $action['playerId'], $power);
          }
        }
      }
      return;
    }


    $this->game->log->addAction('stealPower', ['playerId' => $opponent->getId() ]);
    foreach($opponent->getPowers() as $power){
      $this->game->cards->moveCard($power->getId(), 'hand', $this->game->getActivePlayerId());
      $this->notify(true, $opponent->getId(), $power);
    }
  }
}
