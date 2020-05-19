<?php

class Triton extends SantoriniPower
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return TRITON;
  }

  public static function getName() {
    return clienttranslate('Triton');
  }

  public static function getTitle() {
    return clienttranslate('God of the Waves');
  }

  public static function getText() {
    return [
      clienttranslate("Your Move: Each time your Worker moves into a perimeter space, it may immediately move again.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [HARPIES];
  }

  public static function isGoldenFleece() {
    return true;
  }


  /* * */
  public function hasMovedOnPerimeter()
  {
    $move = $this->game->log->getLastMove($this->playerId);
    return $this->game->board->isPerimeter($move['to']);
  }


  public function argPlayerMove(&$arg)
  {
    // No move before => usual rule
    $move = $this->game->log->getLastMove();
    if($move == null)
      return;

    Utils::filterWorkersById($arg, $move['pieceId']);
    $arg['skippable'] = true;
  }

  public function stateAfterMove()
  {
    return $this->hasMovedOnPerimeter()?  'moveAgain' : null;
  }

}
