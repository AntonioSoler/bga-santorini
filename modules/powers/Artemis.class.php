<?php

class Artemis extends Power
{
  public static function getId() {
    return ARTEMIS;
  }

  public static function getName() {
    return clienttranslate('Artemis');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of the Hunt');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Your Worker may move one additional time, but not back to its initial space.")
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
  public function argPlayerMove(&$arg)
  {
    $move = $this->game->log->getLastMove();
    // No move before => usual rule
    if($move == null)
      return;

    // Otherwise, let the player do a second move (not mandatory)
    $arg['skippable'] = true;
    $arg['workers'] = array_values(array_filter($arg['workers'], function($worker) use ($move){
      return $worker['id'] == $move['pieceId'];
    }));
    // TODO : handle the case where $arg[workers] is empty ?

    $worker = &$arg['workers'][0];
    $worker['works'] = array_values(array_filter($worker['works'], function($space) use ($move){
      return !$this->game->board->isSameSpace($space, $move['from']);
    }));
  }

  public function stateAfterMove()
  {
    return count($this->game->log->getLastMoves()) == 1? 'moveAgain' : null;
  }
}
