<?php

class Hera extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HERA;
    $this->name  = clienttranslate('Hera');
    $this->title = clienttranslate('Goddess of Marriage');
    $this->text  = [
      clienttranslate("[Opponent's Turn:] An opponent cannot win by moving into a perimeter space."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 5;

    $this->implemented = true;
  }

  /* * */

  public function checkOpponentWinning(&$arg)
  {
    if (!$arg['win']) {
      return;
    }

    $work = $this->game->log->getLastWinableWork();
    if ($work == null || $work['action'] != 'move') {
      return;
    }

    if (!$this->game->board->isPerimeter($work['to'])) {
      return;
    }

    // Stop the win
    $arg['win'] = false;
    $arg['winStats'] = [[$this->playerId, 'usePower']];
    
    
    // Hecate: do not reveal opponent location
    $opponent = $this->game->playerManager->getPlayer($arg['pId']);
    $hecate = false;
    $powers = $opponent->getPowers();
    foreach ($powers as $power) {
    if ($power->getId() == HECATE) {
      $hecate = true;
      break;
    }
    }
    
    if ($hecate){
      $this->game->notifyPlayer($arg['pId'], 'message', clienttranslate('${power_name}: ${player_name} cannot win by moving into a perimeter space'), [
          'i18n' => ['power_name'],
          'power_name' => $this->getName(),
          'player_name' => $this->game->getActivePlayerName(),
        ]);
    }
    else{
      $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} cannot win by moving into a perimeter space'), [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $this->game->getActivePlayerName(),
        ]);
    }
  }
}
