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
      clienttranslate("Start of Your Turn: Place a block or dome on your God Power card."),
      clienttranslate("Your Build: Your Worker cannot build as normal. Instead, your Worker may build any number of times (even zero) using blocks / domes collected on your God Power card. At any time, any player may exchange a block / dome on the God Power card for dome or a block of a different shape.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;

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
