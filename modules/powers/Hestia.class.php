<?php

class Hestia extends SantoriniPower
{
  public function __construct($game, $playerId){
    parent::__construct($game, $playerId);
    $this->id    = HESTIA;
    $this->name  = clienttranslate('Hestia');
    $this->title = clienttranslate('Goddess of Hearth and Home');
    $this->text  = [
      clienttranslate("Your Build: Your Worker may build one additional time, but this cannot be on a perimeter space.")
    ];
    $this->players = [2, 3, 4];
    
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */
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
