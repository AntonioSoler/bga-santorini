<?php

class Morpheus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = MORPHEUS;
    $this->name  = clienttranslate('Morpheus');
    $this->title = clienttranslate('God of Dreams');
    $this->text  = [
      clienttranslate("[Start of Your Turn:] Place a coin on your God Power card."),
      clienttranslate("[Your Build:] Your Worker cannot build as normal. Instead, spend any number of coins from your God Power card (even zero) and build that many times."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 1;

    $this->implemented = true;
  }

  /* * */

  public function getUIData()
  {
    $data = parent::getUIData();
    $data['counter'] = ($this->playerId != null) ? $this->computeStock() : 0;
    return $data;
  }

  public function computeStock()
  {
    $stock = self::getObjectFromDb("SELECT (SELECT COUNT(*) FROM log WHERE action = 'morpheusStart') - (SELECT COUNT(*) FROM log WHERE action = 'morpheusBuild') AS stock FROM DUAL");
    return intval($stock);
  }

  public function startPlayerTurn()
  {
    $this->game->log->addAction('morpheusStart');
    $this->updateUI();
  }

  public function argPlayerMove(&$arg)
  {
    $this->updateUI();
  }

  public function argPlayerBuild(&$arg)
  {
    $arg['skippable'] = true;
  }

  public function playerBuild($worker, $work)
  {
    $this->game->log->addAction('morpheusBuild');
    return false;
  }

  public function afterPlayerBuild($worker, $work)
  {
    $this->updateUI();
  }

  public function stateAfterBuild()
  {
    return $this->computeStock() > 0 ? 'build' : null;
  }

  public function endPlayerTurn()
  {
    $value = abs(count($this->game->log->getLastBuilds()) - 1);
    if ($value != 0) {
      $stats = [[$this->playerId, 'usePower', $value]];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
