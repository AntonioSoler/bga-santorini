<?php

class Dionysus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = DIONYSUS;
    $this->name  = clienttranslate('Dionysus');
    $this->title = clienttranslate('God of Wine');
    $this->text  = [
      clienttranslate("Your Build: Each time a Worker you control creates a Complete Tower, you may take an additional turn using an opponent Worker instead of your own. No player can win during these additional turns.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
  }

  /* * */
  
  
  // TODO: filter only Opponent workers in Athena Aphrodite Hypnus Limus Hades
  public function argPlayerMove(&$arg)
  { 
    $move = $this->game->log->getLastMove();
    // No move before => usual rule
    if ($move == null) {
      return;
    }
        
    $arg['workers'] = $this->game->board->getPlacedOpponentWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker["works"] = $this->board->getNeighbouringSpaces($worker, 'move');
    }
    Utils::cleanWorkers($arg);
    $arg['skippable' => true];

  }
  
  
  public function argPlayerBuild(&$arg)
  {
    $build = $this->game->log->getLastBuild();
    // No build before => usual rule
    if ($build == null) {
      return;
    }
  
    $arg['workers'] = $this->game->board->getPlacedOpponentWorkers();
    foreach ($arg['workers'] as &$worker) {
      $worker["works"] = $this->board->getNeighbouringSpaces($worker, 'build');
    }
    Utils::cleanWorkers($arg);
  }



  public function stateAfterBuild()
  {
    $build = $this->game->log->getLastBuild();
    return ($build['to']['z'] == 3) ? 'move' : null;
  }
  
  public function checkPlayerWinning(&$arg)
  {
    $arg['win'] = false;
  }




}






