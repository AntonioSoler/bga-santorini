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
      clienttranslate("Start of Your Turn: Place a coin on your God Power card."),
      clienttranslate("Your Build: Your Worker cannot build as normal. Instead, spend any number of coins from your God Power card (even zero) and build that many times."),
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
    $data['stock'] = $this->computeStock();
    return $data;
  }

  public function computeStock()
  {
    $rounds = self::getObjectFromDb("SELECT COUNT(*) as n FROM log WHERE `action` = 'morpheusStart'");
    $builds = self::getObjectFromDb("SELECT COUNT(*) as n FROM log WHERE `action` = 'morpheusBuild'");
    return $rounds['n'] - $builds['n'];
  }

  public function updateUI()
  {
    $this->game->notifyAllPlayers('updatePowerUI', '', [
      'playerId' => $this->playerId,
      'powerId' => $this->getId(),
      'stock' => $this->computeStock()
    ]);
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
    return $this->computeStock() > 0 ? 'buildAgain' : null;
  }
}
