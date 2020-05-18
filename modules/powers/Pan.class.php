<?php

class Pan extends Power
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return PAN;
  }

  public static function getName() {
    return clienttranslate('Pan');
  }

  public static function getTitle() {
    return clienttranslate('God of the Wild');
  }

  public static function getText() {
    return [
      clienttranslate("Win Condition: You also win if your Worker moves down two or more levels.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HADES];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */
  public function checkPlayerWinning(&$arg) {
    if($arg['win'])
      return;

    $move = $this->game->log->getLastWork();
    if($move == null || $move['action'] != 'move' || $move['to']['z'] > $move['from']['z'] - 2)
      return;

    $arg['win'] = true;
    $arg['msg'] = clienttranslate('Pan won by moving down.');
  }

}
