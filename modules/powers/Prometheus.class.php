<?php

class Prometheus extends SantoriniPower
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return PROMETHEUS;
  }

  public static function getName() {
    return clienttranslate('Prometheus');
  }

  public static function getTitle() {
    return clienttranslate('Titan Benefactor of Mankind');
  }

  public static function getText() {
    return [
      clienttranslate("Your Turn: If your Worker does not move up, it may build both before and after moving.")
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
  public function stateStartTurn(){
    return 'build';
  }


  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    $move  = $this->game->log->getLastMove();
    // Already built or move before => usual rule
    if($build != null || $move != null)
      return;

    $arg['skippable'] = true;
    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach($arg['workers'] as &$worker){
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');
    }
  }


  public function argPlayerMove(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if($build == null)
      return;

    // Otherwise, the player has to move with the worker that built
    Utils::filterWorkersById($arg, $build['pieceId']);
    Utils::filterWorks($arg, function($space, $worker){
      return $space['z'] <= $worker['z'];
    });
  }


  public function stateAfterBuild()
  {
    return is_null($this->game->log->getLastMove())? 'move' : null;
  }

  public function stateAfterSkip()
  {
    // TODO : check the state is "build" ?
    return is_null($this->game->log->getLastMove())? 'move' : null;
  }

}
