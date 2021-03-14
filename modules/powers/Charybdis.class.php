<?php
class Charybdis extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHARYBDIS;
    $this->name  = clienttranslate('Charybdis');
    $this->title = clienttranslate('Whirlpool Monster');
    $this->text  = [
      clienttranslate("[Setup:] Place 2 Whirlpool Tokens on your God Power card."),
      clienttranslate("[End of Your Turn:] You may place a Whirlpool Token from your God Power card on any unoccupied space on the board."),
      clienttranslate("[Any Time:] If a Worker moves onto a Whirlpool and the other Whirlpool is on the board in an unoccupied space, it is forced to the other Whirlpool's space. In this case, the player cannot win by moving their Worker to the first Whirlpool's space but can win as if it had moved up to the second space. Whirlpool Tokens built on or removed are returned to your God Power card."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 6;

    $this->implemented = true;
  }

  /* * */

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['counter'] = $this->playerId != null ? count($this->getUnplacedTokens()) : 0;
    return $data;
  }

  public function getTokens()
  {
    return $this->game->board->getPiecesByType('tokenWhirlpool');
  }

  public function getPlacedTokens()
  {
    return $this->game->board->getPiecesByType('tokenWhirlpool', null, 'board');
  }

  public function getUnplacedTokens()
  {
    return $this->game->board->getPiecesByType('tokenWhirlpool', null, 'hand');
  }

  public function setup()
  {
    $this->getPlayer()->addToken('tokenWhirlpool');
    $this->getPlayer()->addToken('tokenWhirlpool');
    $this->updateUI();
  }

  public function stateAfterBuild()
  {
    $tokens = $this->getUnplacedTokens();
    return count($tokens) > 0 ? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $tokens = $this->getUnplacedTokens();
    $build = $this->game->log->getLastBuild();
    $worker = $this->game->board->getPiece($build['pieceId']);

    if (count($tokens) < 1) {
      $worker['works'] = [];
      $arg['workers'] = [$worker];
      return;
    }

    $worker['works'] = $this->game->board->getAccessibleSpaces('build');

    $placedToken = $this->getPlacedTokens();
    if (count($placedToken) > 0) {
      Utils::filter($worker['works'], function ($space) use ($placedToken) {
        return !($space['x'] == $placedToken[0]['x'] && $space['y'] == $placedToken[0]['y']);
      });
    }

    $arg['workers'] = [$worker];
  }

  public function usePower($action)
  {
    $token = $this->getUnplacedTokens()[0];
    $space = $action[1];
    $this->placeToken($token, $space);
    $this->updateUI();
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function afterMove($worker, $work)
  {
    $tokens = $this->getPlacedTokens();
    if (count($tokens) < 2)
      return;

    // check if a worker moved on a token
    $startindex = $this->game->board->isSameSpace($work, $tokens[0]) ? 0 : ($this->game->board->isSameSpace($work, $tokens[1]) ? 1 : null);
    if (is_null($startindex)) {
      return;
    }

    $endToken = $tokens[1 - $startindex];

    $acc = $this->game->board->getAccessibleSpaces('build');
    Utils::filter($acc, function ($space) use ($endToken) {
      return ($space['x'] == $endToken['x'] && $space['y'] == $endToken['y']);
    });
    if (count($acc) == 0) {
      return;
    }

    // force to the whirlpool, while logging a special action and animating the teleport
    $target = $acc[0];
    $target['direction'] = $work['direction'];
    $forceTarget = $target;
    $forceTarget['z'] = $forceTarget['z'] - 1;
    $forceTarget['id'] = $worker['id'];
    $work['id'] = $worker['id'];

    $stats = [[$this->playerId, 'usePower']];
    $this->game->log->addForce($work, $target, $stats);
    $this->game->log->addWhirlpoolMove($forceTarget, $target); // add special log that mimics a move and is triggered only when checking wins
    $this->game->board->setPieceAt($work, $target, $worker['location']);

    // Notify force
    $this->game->notifyWithSecret($worker, 'pieceRemoved', $this->game->msg['powerForce'], [
      'i18n' => ['power_name', 'level_name'],
      'piece' => $work,
      'space' => $forceTarget,
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
      'player_name2' => $this->game->playerManager->getPlayer()->getName(),
      'level_name' => $this->game->levelNames[intval($target['z'])],
      'coords' => $this->game->board->getMsgCoords($work, $target),
      'duration' => 200,
    ]);

    $worker['x'] = $forceTarget['x'];
    $worker['y'] = $forceTarget['y'];
    $worker['z'] = $forceTarget['z'];
    $this->game->notifyWithSecret($worker, 'workerPlaced', '', [
      'piece' => $worker,
      'animation' => 'none',
      'duration' => INSTANT,
    ]);
    // Notify move up
    $this->game->notifyWithSecret($worker, 'workerMoved', '', [
      'piece' => $work,
      'space' => $target,
      'duration' => 500,
    ]);
  }


  public function afterOpponentMove($worker, $work)
  {
    $this->afterMove($worker, $work);
  }

  public function afterPlayerMove($worker, $work)
  {
    $this->afterMove($worker, $work);
  }
}
