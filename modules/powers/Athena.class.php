<?php

class Athena extends Power
{
  public static function getId() {
    return ATHENA;
  }

  public static function getName() {
    return clienttranslate('Athena');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Wisdom');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: If one of your Workers moved up on your last turn, opponent Workers cannot move up this turn.")
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

  /* * */

  public function hasMovedUp()
  {
    $moves = $this->game->log->getLastMoves($this->playerId);
    return array_reduce($moves, function($movedUp, $move){
      return $movedUp || $move['to']['z'] > $move['from']['z'];
    }, false);
  }

  public function argOpponentMove(&$arg)
  {
    if(!$this->hasMovedUp())
      return;


    foreach($arg["workers"] as &$worker)
      $worker['accessibleSpaces'] = array_values(array_filter($worker['accessibleSpaces'], function($space) use ($worker){
        return $space['z'] <= $worker['z'];
      }));
  }
}
