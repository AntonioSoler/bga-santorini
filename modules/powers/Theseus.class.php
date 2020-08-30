<?php

class Theseus extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = THESEUS;
    $this->name  = clienttranslate('Theseus');
    $this->title = clienttranslate('Slayer of the Minotaur');
    $this->text  = [
      clienttranslate("[End of Your Turn:] [Once], if any of your Workers is exactly 2 levels below any neighboring opponent Workers, remove one of those opponent Workers from play."),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 7;

    $this->implemented = true;
  }

  /* * */

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

    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      foreach ($oppWorkers as $worker2) {
        if ($worker['z'] == $worker2['z'] - 2 && $this->game->board->isNeighbour($worker, $worker2)) {
          $worker['works'][] = SantoriniBoard::getCoords($worker2, 0, true);
        }
      }
    }
  }

  public function usePower($action)
  {
    $space = $action[1];
    $worker = $this->game->board->getPiece($space['id']);
    $this->game->playerKill($worker, $this->getName(), false);
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }
}
