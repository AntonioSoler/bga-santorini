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
  protected $golden;
  protected $newRule = false;
  protected $implemented = false;

  public function getId() { return $this->id; }
  public function getName() { return $this->name; }
  public function getTitle() { return $this->title; }
  public function getText() { return $this->text; }
  public function getPlayers() { return $this->players; }
  public function isGoldenFleece() { return $this->golden; }
  public function hasNewRule() { return $this->newRule; }
  public function isSimple() { return $this->id <= 10; }

  public function getUiData()
  {
    return [
      'id'        => $this->id,
      'name'      => $this->name,
      'title'     => $this->title,
      'text'      => $this->text,
      'hero'      => get_parent_class($this) == 'SantoriniHeroPower',
      'newRule'   => $this->newRule? 'new-rule' : '',
      'implemented' => $this->implemented? 'implemented' : 'not-implemented',
    ];
  }

  public function isSupported($nPlayers, $optionPowers)
  {
    $isHero = $this instanceof SantoriniHeroPower;
    return in_array($nPlayers, $this->getPlayers())
      && (($optionPowers == GODS_AND_HEROES)
        || ($optionPowers == SIMPLE && $this->isSimple())
        || ($optionPowers == GODS && !$isHero)
        || ($optionPowers == HEROES && $isHero)
        || ($optionPowers == GOLDEN_FLEECE && $this->isGoldenFleece()));
  }

  public function setup($player)
  {
  }

  public function stateStartOfTurn()  { return null; }
  public function startPlayerTurn() {}
  public function startOpponentTurn() {}

  public function argPlayerMove(&$arg)  {  }
  public function argOpponentMove(&$arg)  {  }
  public function playerMove($worker, $work) { return false; }
  public function afterPlayerMove($worker, $work) { }
  public function afterOpponentMove($worker, $work) { }

  public function argPlayerBuild(&$arg) { }
  public function argOpponentBuild(&$arg)  {  }
  public function playerBuild($worker, $work) { return false; }
  public function afterPlayerBuild($worker, $work) { }
  public function afterOpponentBuild($worker, $work) { }

  public function stateAfterMove() { return null; }
  public function stateAfterBuild() { return null; }
  public function stateAfterSkip()  { return null;  }

//  public function stateEndOfTurn()  { return null; }
  public function endPlayerTurn() {}
  public function endOpponentTurn() {}

  public function checkPlayerWinning(&$arg) { }
  public function checkOpponentWinning(&$arg) { }
}
