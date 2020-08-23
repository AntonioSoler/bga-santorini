<?php

class Europa extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = EUROPA;
    $this->name  = clienttranslate('Europa & Talus');
    $this->title = clienttranslate('Queen & Guardian Automaton');
    $this->text  = [
      clienttranslate("[Setup:] Place the Talus Token on your God Power card."),
      clienttranslate("[End of Your Turn:] You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved."),
      clienttranslate("[Any Time:] All players treat the space containing the Talus Token as if it contains only a dome."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 9;

    $this->implemented = true;
  }

  /* * */

  public function getToken()
  {
    return $this->game->board->getPiecesByType('tokenTalus')[0];
  }

  public function setup()
  {
    $this->getPlayer()->addToken('tokenTalus');
  }

  public function stateAfterBuild()
  {
    $arg = [];
    $this->argUsePower($arg);
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : null;
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    $move = $this->game->log->getLastMove();
    Utils::filterWorkersById($arg, $move['pieceId']);
    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
    }
  }

  public function usePower($action)
  {
    $token = $this->getToken();
    $space = $action[1];
    if ($token['location'] == 'hand') {
      $this->placeToken($token, $space);
    } else {
      $this->replaceToken($token, $space);
    }
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }
}
