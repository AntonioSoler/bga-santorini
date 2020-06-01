<?php

/*
 * StatManager : handle all the stats
 */
class StatManager extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }


  public function init($players)
  {
    foreach ($players as $pId => $player) {
      $this->game->initStat('player', 'level_0', 0, $pId);
      $this->game->initStat('player', 'level_1', 0, $pId);
      $this->game->initStat('player', 'level_2', 0, $pId);
      $this->game->initStat('player', 'level_3', 0, $pId);
      $this->game->initStat('player', 'moves', 0, $pId);
    }

  	$this->game->initStat('table', 'buildings', 0);
    $this->game->initStat('table', 'moves', 0);
  }


  public function playerMove($worker, $space)
  {
    $pId = $this->game->getActivePlayerId();
    $this->game->incStat(1, 'moves');
    $this->game->incStat(1, 'moves', $pId);
  }


  public function playerBuild($worker, $space)
  {
    $pId = $this->game->getActivePlayerId();
    $this->game->incStat(1, 'buildings');
    $this->game->incStat(1, 'level_'.$space['arg'] , $pId);
  }
}
