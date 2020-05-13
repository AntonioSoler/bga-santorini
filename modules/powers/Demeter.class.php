<?php

class Demeter extends Power
{
  public static function getId() {
    return DEMETER;
  }

  public static function getName() {
    return clienttranslate('Demeter');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Harvest');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional time, but not on the same space.")
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
  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if($build == null)
      return;

    // Otherwise, let the player do a second build (not mandatory)
    $arg['skippable'] = true;
    $arg['verb'] = clienttranslate('can');
    $arg['workers'] = array_values(array_filter($arg['workers'], function($worker) use ($move){
      return $worker['id'] == $build['pieceId'];
    }));

    foreach($arg['workers'] as &$worker){
      $worker['accessibleSpaces'] = array_values(array_filter($worker['accessibleSpaces'], function($space) use ($move){
        return !$this->game->board->isSameSpace($space, $move['to']);
      }));
    }
  }


  public function stateAfterBuild()
  {
    return count($this->game->log->getLastBuilds()) == 1? 'buildAgain' : null;
  }
}
