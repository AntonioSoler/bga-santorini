<?php

abstract class SantoriniPower extends APP_GameClass
{

  protected $game;
  protected $playerId;

  public function __construct($game, $playerId)
  {
    $this->game = $game;
    $this->playerId = $playerId;
  }

  public function isImplemented(){ return false; }

  public function getUiData()
  {
    return [
      'id'        => $this->getId(),
      'name'      => $this->getName(),
      'title'     => $this->getTitle(),
      'text'      => $this->getText(),
      'hero'      => get_parent_class($this) == 'SantoriniHeroPower',
      'implemented' => $this->isImplemented()? 'implemented' : 'not-implemented',
    ];
  }

  public function isSupported($nPlayers, $optionPowers)
  {
    $isHero = $this instanceof SantoriniHeroPower;
    return in_array($nPlayers, $this->getPlayers())
      && (($optionPowers == GODS_AND_HEROES)
        || ($optionPowers == SIMPLE && $this->getId() <= 10)
        ||  ($optionPowers == GODS && !$isHero)
        ||  ($optionPowers == HEROES && $isHero)
        || ($optionPowers == GOLDEN_FLEECE && $this->isGoldenFleece()));
  }

  public function setup($player)
  {
  }

  public function stateStartTurn()  { return null; }

  public function stateAfterSkip()  { return null;  }

  public function argPlayerMove(&$arg)  {  }
  public function argOpponentMove(&$arg)  {  }
  public function playerMove($worker, $work) { return false; }
  public function afterPlayerMove($worker, $work) { }
  public function afterOpponentMove($worker, $work) { }
  public function stateAfterMove() { return null; }

  public function argPlayerBuild(&$arg) { }
  public function argOpponentBuild(&$arg)  {  }
  public function playerBuild($worker, $work) { return false; }
  public function afterPlayerBuild($worker, $work) { }
  public function afterOpponentBuild($worker, $work) { }
  public function stateAfterBuild() { return null; }

  public function checkPlayerWinning(&$arg) { }
  public function checkOpponentWinning(&$arg) { }
}
