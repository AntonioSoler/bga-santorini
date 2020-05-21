<?php

class Graeae extends Power
{
  public static function getId() {
    return GRAEAE;
  }

  public static function getName() {
    return clienttranslate('Graeae');
  }

  public static function getTitle() {
    return clienttranslate('The Gray Hags');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: When placing your Workers, place 3 of your color."),
      clienttranslate("Your Build: Build with a worker that did not move. *** TODO: updated")
    ];
  }

  public static function getPlayers() {
    return [2, 3];
  }

  public static function getBannedIds() {
    return [NEMESIS];
  }

  public static function isGoldenFleece() {
    return false; 
  }


  // TODO: setup 3 workers
  public function argPlayerBuild(&$arg)
  {
    $move = $this->game->log->getLastMove();
    
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    Utils::filterWorkers($arg, function($worker) use ($move){
      return $worker['id'] != $move['pieceId'];
    });
    foreach($arg['workers'] as &$worker){
      $worker['works'] = $this->board->getNeighbouringSpaces($worker, 'build');
    }
  }

  /* * */

}
  
