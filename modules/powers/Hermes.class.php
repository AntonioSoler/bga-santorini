<?php

class Hermes extends Power
{
  public static function getId() {
    return HERMES;
  }

  public static function getName() {
    return clienttranslate('Hermes');
  }

  public static function getTitle() {
    return clienttranslate('God of Travel');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: If your Workers do not move up or down, they may each move any number of times (even zero), and then either builds.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HARPIES];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */
  public function hasMovedUpOrDown()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function($movedUp, $move){
      return $movedUp || $move['to']['z'] != $move['from']['z'];
    }, false);
  }


  /* * */
  public function argPlayerMove(&$arg)
  {
    $arg['skippable'] = true;

    // No move before => usual rule
    $move = $this->game->log->getLastMove();
    if($move == null)
      return;

    // Otherwise, let the player do a second move but on same height
    $this->filterWorks($arg, function($space, $worker){
      return $space['z'] == $worker['z'];
    });
  }

  public function stateAfterMove()
  {
    return $this->hasMovedUpOrDown()? null : 'moveAgain';
  }


  public function argPlayerBuild(&$arg)
  {
    // Moved up/down => usual rule
    if($this->hasMovedUpOrDown())
      return;

    // Otherwise, let the player build with any worker
    $arg['workers'] = $this->game->board->getPlacedWorkers($this->game->getActivePlayerId());
    foreach($arg['workers'] as &$worker){
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
    }
  }


}
