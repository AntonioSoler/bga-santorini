<?php

class Demeter extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = DEMETER;
    $this->name  = clienttranslate('Demeter');
    $this->title = clienttranslate('Goddess of the Harvest');
    $this->text  = [
      clienttranslate("Your Build: Your Worker may build one additional time, but not on the same space.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 55;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if ($build == null) {
      return;
    }

    // Otherwise, let the player do a second build (not mandatory)
    $arg['skippable'] = true;
    Utils::filterWorkersById($arg, $build['pieceId']);
    Utils::filterWorks($arg, function ($space, $worker) use ($build) {
      return !$this->game->board->isSameSpace($space, $build['to']);
    });
  }


  public function stateAfterBuild()
  {
    return count($this->game->log->getLastBuilds()) == 1 ? 'buildAgain' : null;
  }
}
