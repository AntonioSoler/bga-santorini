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


  protected $id = 0;
  protected $name = '';
  protected $title = '';
  protected $text;
  protected $players;
  protected $banned;
  protected $golden;
  protected $implemented = false;

  public function getId() { return $this->id; }
  public function getName() { return $this->name; }
  public function getTitle() { return $this->title; }
  public function getText() { return $this->text; }
  public function getPlayers() { return $this->players; }
  public function getBannedIds() { return $this->banned; }
  public function isGoldenFleece() { return $this->golden; }

  public function getUiData()
  {
    return [
      'id'        => $this->id,
      'name'      => $this->name,
      'title'     => $this->title,
      'text'      => $this->text,
      'hero'      => get_parent_class($this) == 'SantoriniHeroPower',
      'implemented' => $this->implemented? 'implemented' : 'not-implemented',
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
