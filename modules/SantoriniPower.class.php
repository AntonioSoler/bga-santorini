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

  public function isHero()
  {
    return $this instanceof SantoriniHeroPower;
  }

  public function isImplemented()
  {
    return $this->implemented;
  }

  public function getUiData()
  {
    return [
      'id'          => $this->id,
      'name'        => $this->name,
      'title'       => $this->title,
      'text'        => $this->text,
      'hero'        => $this->isHero(),
      'golden'      => $this->golden,
      'playerCount' => $this->playerCount,
    ];
  }

  public function isSupported($nPlayers, $optionPowers)
  {
    return $this->implemented
      && in_array($nPlayers, $this->getPlayerCount())
      && (($optionPowers == GODS_AND_HEROES)
        || ($optionPowers == SIMPLE && $this->isSimple())
        || ($optionPowers == GODS && !$this->isHero())
        || ($optionPowers == HEROES && $this->isHero())
        || ($optionPowers == GOLDEN_FLEECE && $this->isGoldenFleece()));
  }

  public function setPlayerId($newPlayerId)
  {
    $this->playerId = $newPlayerId;
  }

  public function getPlayerId()
  {
    return $this->playerId;
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

    // Notify
    $this->game->notifyAllPlayers('workerPlaced', $this->game->msg['powerPlacePiece'], [
      'i18n' => ['power_name', 'piece_name'],
      'piece' => $worker,
      'piece_name' => $this->game->pieceNames[$worker['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
      'coords' => $this->game->board->getMsgCoords($space),
    ]);
  }


  public function placeToken($token, $space)
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->board->setPieceAt($token, $space);
    $token['x'] = $space['x'];
    $token['y'] = $space['y'];
    $token['z'] = $space['z'];
    $this->game->log->addPlaceToken($token, $this->id, $stats);

    // Notify
    $this->game->notifyAllPlayers('workerPlaced', $this->game->msg['powerPlacePiece'], [
      'i18n' => ['power_name', 'piece_name'],
      'piece' => $token,
      'piece_name' => $this->game->pieceNames[$token['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($token),
    ]);
  }

  public function moveToken($token, $space)
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->board->setPieceAt($token, $space);
    $this->game->log->addForce($token, $space, $stats);

    // Notify
    $this->game->notifyAllPlayers('workerMoved', $this->game->msg['powerMovePiece'], [
      'i18n' => ['power_name', 'piece_name'],
      'piece' => $token,
      'piece_name' => $this->game->pieceNames[$token['type']],
      'space' => $space,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($token, $space),
    ]);
  }

  public function removePiece($piece)
  {
    self::DbQuery("UPDATE piece SET location = 'box' WHERE id = {$piece['id']}");
    $this->game->log->addRemoval($piece);

    // Notify
    $this->game->notifyAllPlayers('pieceRemoved', $this->game->msg['powerRemovePiece'], [
      'i18n' => ['power_name', 'piece_name'],
      'piece' => $piece,
      'piece_name' => $this->game->pieceNames[$piece['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($piece),
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

  public function argTeammatePlaceWorker(&$arg)
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

  public function startTeammateTurn()
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

  public function argTeammateMove(&$arg)
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

  public function afterTeammateMove($worker, $work)
  {
  }

  public function afterOpponentMove($worker, $work)
  {
  }

  public function argPlayerBuild(&$arg)
  {
  }

  public function argTeammateBuild(&$arg)
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

  public function afterTeammateBuild($worker, $work)
  {
  }

  public function afterOpponentBuild($worker, $work)
  {
  }

  public function stateAfterMove()
  {
    return null;
  }

  // only Gaea
  public function stateAfterOpponentBuild()
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

  public function preEndTeammateTurn()
  {
  }

  public function preEndOpponentTurn()
  {
  }

  public function endPlayerTurn()
  {
  }

  public function endTeammateTurn()
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
