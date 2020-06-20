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
      clienttranslate("[Your Build:] Each time a Worker you control creates a Complete Tower, you may take an additional turn using an opponent Worker instead of your own. No player can win during these additional turns.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 24;

    $this->implemented = true;
  }

  /* * */
  // TODO: filter only Opponent workers in Athena Aphrodite Hypnus Limus Hades
  public function argPlayerMove(&$arg)
  {
    // Usual turn => usual rule
    if (!$this->game->log->isAdditionalTurn()) {
      return;
    }

    $arg = $this->game->argPlayerWork('move', $this->game->board->getPlacedOpponentWorkers());
    $arg['skippable'] = true;
  }


  public function stateAfterSkip()
  {
    return 'endturn';
  }

  public function argPlayerBuild(&$arg)
  {
    // Usual turn => usual rule
    if (!$this->game->log->isAdditionalTurn()) {
      return;
    }

    $arg = $this->game->argPlayerWork('build', $this->game->board->getPlacedOpponentWorkers());
    $move = $this->game->log->getLastMove();
    if (!is_null($move)) {
      Utils::filterWorkersById($arg, $move['pieceId']);
    }
  }


  public function playerBuild($worker, $work)
  {
    if ($work['z'] == 3) {
      $action = $this->game->log->getLastAction("additionalTurn");
      $n = $action == null ? 0 : ($action['n'] + 1);
      $this->game->log->addAction("towerCompleted", [], ['n' => $n]);
    }

    return false;
  }


  public function stateEndOfTurn()
  {
    $tower = $this->game->log->getLastAction("towerCompleted");
    $action = $this->game->log->getLastAction("additionalTurn");
    if ($tower == null || ($action != null && $tower['n'] <= $action['n'])) {
      return null;
    }

    $this->game->log->addAction("additionalTurn", [], ['n' => $action == null ? 0 : ($action['n'] + 1)]);
    return 'additionalTurn';
  }


  protected function checkWinning(&$arg)
  {
    if ($this->game->log->isAdditionalTurn()) {
      $arg['win'] = false;
      unset($arg['winStats']);
    }
  }

  public function checkPlayerWinning(&$arg)
  {
    $this->checkWinning($arg);
  }

  public function checkOpponentWinning(&$arg)
  {
    $this->checkWinning($arg);
  }
}
