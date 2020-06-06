<?php

class Poseidon extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = POSEIDON;
    $this->name  = clienttranslate('Poseidon');
    $this->title = clienttranslate('God of the Sea');
    $this->text  = [
      clienttranslate("[End of Your Turn:] If your unmoved Worker is on the ground level, it may build up to three times.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 35;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if ($build == null){
      return;}

    // Otherwise, let the unmoved worker, which is on the ground floor, do another build (not mandatory)
    $move = $this->game->log->getLastMove(); // may be a different worker because of opponent powers
    $arg['skippable'] = true;

    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    foreach ($arg['workers'] as &$worker) {
      if ($worker['id'] != $move['pieceId']){
        $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, 'build');}
    }
  }


  public function stateAfterBuild()
  {
    // 1 normal build + 3 possible ones
    if (count($this->game->log->getLastBuilds()) >= 4){
      return null;}

    $move = $this->game->log->getLastMove();
    $workers = $this->game->board->getPlacedActiveWorkers();

    // the power does not apply with 1 worker and is unclear with more workers
    if (count($workers) != 2){
      return null;}

    Utils::filterWorkers($workers, function ($worker) use ($move) {
      return ($worker['id'] != $move['pieceId']) && $worker['z'] == 0;
    });
    return empty($workers) ? null : 'buildAgain';
  }
}
