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
  protected $playerCount;
  protected $golden;
  protected $orderAid;
  protected $implemented = false;

  public function getId()
  {
    return $this->id;
  }
  public function getName()
  {
    return $this->name;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function getText()
  {
    return $this->text;
  }
  public function getPlayerCount()
  {
    return $this->playerCount;
  }
  public function getOrderAid()
  {
    return $this->orderAid;
  }
  public function isGoldenFleece()
  {
    return $this->golden;
  }
  public function isSimple()
  {
    return $this->id <= 10;
  }

  public function getUiData()
  {
    return [
      'id'        => $this->id,
      'name'      => $this->name,
      'title'     => $this->title,
      'text'      => $this->text,
      'hero'      => get_parent_class($this) == 'SantoriniHeroPower',
    ];
  }

  public function isSupported($nPlayers, $optionPowers)
  {
    $isHero = $this instanceof SantoriniHeroPower;
    return $this->implemented
      && in_array($nPlayers, $this->getPlayerCount())
      && (($optionPowers == GODS_AND_HEROES)
        || ($optionPowers == SIMPLE && $this->isSimple())
        || ($optionPowers == GODS && !$isHero)
        || ($optionPowers == HEROES && $isHero)
        || ($optionPowers == GOLDEN_FLEECE && $this->isGoldenFleece()));
  }

  public function setPlayerId($newPlayerId)
  {
    $this->playerId = $newPlayerId;
  }

  public function getPlayer()
  {
    return $this->game->playerManager->getPlayer($this->playerId);
  }

  public function updateUI()
  {
    $data = $this->getUiData();
    $this->game->notifyAllPlayers('updatePowerUI', '', [
      'playerId' => $this->playerId,
      'powerId' => $this->id,
      'counter' => $data['counter'],
    ]);
  }

  public function placeWorker($worker, $space)
  {
    $this->game->board->setPieceAt($worker, $space);
    $worker['x'] = $space['x'];
    $worker['y'] = $space['y'];
    $worker['z'] = $space['z'];
    $this->game->log->addPlaceWorker($worker, $this->id);
    $this->game->notifyAllPlayers('workerPlaced', clienttranslate('${power_name}: ${player_name} places a worker (${coords})'), [
      'i18n' => ['power_name'],
      'piece' => $worker,
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
      'coords' => $this->game->board->getMsgCoords($space),
    ]);
  }

  public function setup()
  {
  }

  public function argChooseFirstPlayer(&$arg)
  {
  }

  public function argPlayerPlaceWorker(&$arg)
  {
  }

  public function argOpponentPlaceWorker(&$arg)
  {
  }

  public function stateStartOfTurn()
  {
    return null;
  }

  public function startPlayerTurn()
  {
  }

  public function startOpponentTurn()
  {
  }

  public function argUsePower(&$arg)
  {
  }

  public function stateAfterUsePower()
  {
    return null;
  }

  public function stateAfterSkipPower()
  {
    return null;
  }

  public function argPlayerMove(&$arg)
  {
  }

  public function argOpponentMove(&$arg)
  {
  }

  public function playerMove($worker, $work)
  {
    return false;
  }

  public function afterPlayerMove($worker, $work)
  {
  }

  public function afterOpponentMove($worker, $work)
  {
  }

  public function argPlayerBuild(&$arg)
  {
  }

  public function argOpponentBuild(&$arg)
  {
  }

  public function playerBuild($worker, $work)
  {
    return false;
  }
  public function afterPlayerBuild($worker, $work)
  {
  }

  public function afterOpponentBuild($worker, $work)
  {
  }

  public function stateAfterMove()
  {
    return null;
  }

  public function stateAfterBuild()
  {
    return null;
  }

  public function stateAfterSkip()
  {
    return null;
  }

  public function preEndPlayerTurn()
  {
  }

  public function preEndOpponentTurn()
  {
  }

  public function endPlayerTurn()
  {
  }

  public function endOpponentTurn()
  {
  }

  public function stateEndOfTurn()
  {
    return null;
  }

  public function checkPlayerWinning(&$arg)
  {
  }

  public function checkOpponentWinning(&$arg)
  {
  }
}
