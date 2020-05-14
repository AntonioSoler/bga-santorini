<?php

class Hephaestus extends Power
{
  public static function getId() {
    return HEPHAESTUS;
  }

  public static function getName() {
    return clienttranslate('Hephaestus');
  }

  public static function getTitle() {
    return clienttranslate('God of Blacksmiths');
  }

  public static function getText() {
    return [
      clienttranslate("Your Build: Your Worker may build one additional block (not dome) on top of your first block.")
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
  /* * */
  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if($build == null)
      return;

    // Otherwise, let the player do a second build (not mandatory)
    $arg['skippable'] = true;
    $arg['workers'] = array_values(array_filter($arg['workers'], function($worker) use ($build){
      return $worker['id'] == $build['pieceId'];
    }));

    foreach($arg['workers'] as &$worker){
      $worker['works'] = array_values(array_filter($worker['works'], function($space) use ($build){
        return $this->game->board->isSameSpace($space, $build['to']) && $space['z'] != 3;
      }));
    }
  }


  public function stateAfterBuild()
  {
    return count($this->game->log->getLastBuilds()) == 1? 'buildAgain' : null;
  }

}
