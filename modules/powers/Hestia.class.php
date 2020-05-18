<?php

class Hestia extends Power
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return HESTIA;
  }

  public static function getName() {
    return clienttranslate('Hestia');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Hearth and Home');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional time, but this cannot be on a perimeter space.")
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


  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if($build == null)
      return;

    // Otherwise, let the player do a second build (not mandatory) but not on the perimeter
    $arg['skippable'] = true;
    Utils::filterWorkersById($arg, $build['pieceId']);
    Utils::filterWorks($arg, function($space, $worker){
      return !$this->game->board->isPerimeter($space);
    });
  }


  public function stateAfterBuild()
  {
    if(count($this->game->log->getLastBuilds()) != 1)
      return null;

    $arg = $this->game->argPlayerBuild();
    return !empty($arg['workers'])? 'buildAgain' : null;
  }

}
