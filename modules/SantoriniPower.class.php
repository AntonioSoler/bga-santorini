<?php

abstract class SantoriniPower extends APP_GameClass
{
  public static function compareByName($power1, $power2)
  {
    $name1 = strtolower($power1->name);
    $name2 = strtolower($power2->name);
    if ($name1 == $name2) {
      return 0;
    }
    return ($name1 > $name2) ? +1 : -1;
  }

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
  protected $perfectInformation = true;

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

  public function isAdvanced()
  {
    return !$this->isSimple() && !$this->isHero();
  }

  public function isPerfectInformation()
  {
    return $this->perfectInformation;
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
    if (array_key_exists('counter', $data)) {
      $args = [
        'playerId' => $this->playerId,
        'powerId' => $this->id,
        'counter' => $data['counter'],
      ];
      if (is_array($data['counter'])) {
        // When counter is an array, show different info to each player
        foreach ($data['counter'] as $playerId => $counter) {
          $args['counter'] = $counter;
          if ($playerId != 'all') {
            // Don't notify all or it accidentally reveals secret token location
            // This means 'all' value cannot change before reavled at game end!
            $this->game->notifyPlayer($playerId, 'updatePowerUI', '', $args);
          }
        }
      } else {
        $this->game->notifyAllPlayers('updatePowerUI', '', $args);
      }
    }
  }

  public function placeWorker($worker, $space)
  {
    $this->game->board->setPieceAt($worker, $space);
    $worker = $this->game->board->getPiece($worker['id']);
    $this->game->log->addPlaceWorker($worker, $this);

    // Notify
    // (must translate coords if it is a direction -- Aeolus, Siren)
    $this->game->notifyAllPlayers('workerPlaced', $this->game->msg['powerPlacePiece'], [
      'i18n' => ['power_name', 'piece_name', 'coords'],
      'piece' => $worker,
      'piece_name' => $this->game->pieceNames[$worker['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
      'coords' => $this->game->board->getMsgCoords($space),
    ]);
  }


  public function placeToken($token, $space, $location = 'board')
  {
    $stats = [[$this->playerId, 'usePower']];
    $this->game->board->setPieceAt($token, $space, $location);
    $token = $this->game->board->getPiece($token['id']);
    $this->game->log->addPlaceToken($token, $this, $stats);

    // Notify
    // (must translate coords if it is a direction -- Aeolus, Siren)
    $args = [
      'redacted' => true,
      'i18n' => ['power_name', 'piece_name', 'coords'],
      'piece' => $token,
      'piece_name' => $this->game->pieceNames[$token['type']],
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($token),
    ];

    $this->game->notifyWithSecret($token, 'workerPlaced', $this->game->msg['powerPlacePiece'], $args);
  }

  public function moveToken($token, $space)
  {

    $stats = [[$this->playerId, 'usePower']];
    $this->game->board->setPieceAt($token, $space);
    $this->game->log->addMoveToken($token, $space, $this, $stats);
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
    $this->game->log->addRemoval($piece);
    $this->game->board->removePiece($piece);

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


  public function argPlaceSetup(&$arg)
  {
  }

  public function placeSetup($action)
  {
  }

  public function stateAfterPlaceSetup()
  {
    return 'done';
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

  // Return true to stop the default move
  // Return false or array to do default move (customized by array args)
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

  // Return true to stop the default build
  // Return false or array to do default build (customized by array args)
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
