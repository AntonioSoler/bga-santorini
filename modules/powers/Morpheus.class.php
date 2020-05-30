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
  public function computeStock()
  {
    $round = $this->game->getGameStateValue("currentRound");
    $pId = $this->playerId;
    $builds = self::getObjectFromDb("SELECT COUNT(*) as n FROM log WHERE `action` = 'build' AND `player_id` = '$pId'");
    return $round - $builds['n'];
  }

  public function updateUI()
  {
    $this->game->notifyAllPlayers('updatePowerUI', '', [
      'playerId' => $this->playerId,
      'powerId' => $this->getId(),
      'stock' => $this->computeStock()
    ]);
  }

  public function startOfTurn()
  {
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

  public function afterPlayerBuild($worker, $work)
  {
    $this->updateUI();
  }

  public function stateAfterBuild()
  {
    return $this->computeStock() > 0 ? 'buildAgain' : null;
  }
}
