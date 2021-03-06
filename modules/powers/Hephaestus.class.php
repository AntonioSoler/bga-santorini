<?php

class Hephaestus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HEPHAESTUS;
    $this->name  = clienttranslate('Hephaestus');
    $this->title = clienttranslate('God of Blacksmiths');
    $this->text  = [
      clienttranslate("[Your Build:] Your Worker may build one additional block (not dome) on top of your first block."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 27;

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
      return $this->game->board->isSameSpace($space, $build['to']) && $space['z'] != 3;
    });
  }


  public function stateAfterBuild()
  {
    $builds = $this->game->log->getLastBuilds();
    $count = count($builds);
    if ($count > 1) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
    return ($count == 1 && $builds[0]['to']['z'] <= 1) ? 'build' : null;
  }
}
