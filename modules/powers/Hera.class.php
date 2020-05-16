<?php

class Hera extends Power
{
  public static function getId() {
    return HERA;
  }

  public static function getName() {
    return clienttranslate('Hera');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Marriage');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: An opponent cannot win by moving into a perimeter space.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */
  public function checkOpponentWinning(&$arg){
    if(!$arg['win'])
      return;

    $work = $this->game->log->getLastWork();
    if($work['action'] != 'move')
      return;

    if($this->game->board->isPerimeter($work['to']))
      $arg['win'] = false;
  }
}
