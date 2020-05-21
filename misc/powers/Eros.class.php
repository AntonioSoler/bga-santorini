<?php

class Eros extends Power
{
  public static function getId() {
    return EROS;
  }

  public static function getName() {
    return clienttranslate('Eros');
  }

  public static function getTitle() {
    return clienttranslate('God of Desire');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place your Workers anywhere along opposite edges of the board."),
      clienttranslate("Win Condition: You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game).")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return false; 
  }
  
  // TODO: setup


  
  public function checkPlayerWinning(&$arg) {
    if($arg['win'])
      return;

    $move = $this->game->log->getLastWork();
    $workers = $this->game->board->getPlacedActiveWorkers();

    if($move == null || $move['action'] != 'move' || count($workers != 2) )
      return;

    if ((!$this->game->board->isNeighbour($workers[0], $workers[1])) || $workers[0]['z'] !=  $workers[1]['z'])
      return;

    if ($this->playerManager->getPlayerCount() == 2 && $workers[0]['z'] != 1)
      return;

    $arg['win'] = true;
    if ($this->playerManager->getPlayerCount() == 2)
      $arg['msg'] = clienttranslate('Eros wins by joining its workers at level 1.');
    else
      $arg['msg'] = clienttranslate('Eros wins by joining its workers at the same level.');
  }

}
  
