<?php

class Limus extends Power
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return LIMUS;
  }

  public static function getName() {
    return clienttranslate('Limus');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Famine');
  }

  public static function getText() {
    return [
      clienttranslate("Opponent's Turn: Opponent Workers cannot build on spaces neighboring your Workers, unless building a dome. *** TODO: updated rule")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [TERPSICHORE];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */
  public function argOpponentBuild(&$arg)
  {
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    foreach($myWorkers as &$worker){
      Utils::filterWorks($arg, function($space, $oppworker) use ($worker) {
        // can build only a dome or at a non-neighbouring space
        return $space['arg'] == 3 || !$this->game->board->isNeighbour($space, $worker, 'build');
      });
    }
  }

}
