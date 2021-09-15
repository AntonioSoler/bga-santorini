<?php

class Aeolus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = AEOLUS;
    $this->name  = clienttranslate('Aeolus');
    $this->title = clienttranslate('God of the Winds');
    $this->text  = [
      clienttranslate("[Setup:] Place the Wind Token on your God Power card."),
      clienttranslate("[End of Your Turn:] If the Wind Token is on your God Power card, you may place the Wind Token beside the board and orient it to indicate the direction of the Wind. Otherwise, you may return the Wind Token to your God Power card."),
      clienttranslate("[Any Move:] If the Wind Token is not on your God Power card, Workers cannot move directly into the Wind."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 56;

    $this->implemented = true;
  }

  /* * */

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['counter'] = '--';

    if ($this->playerId == null) {
      return $data;
    }

    $token = $this->getToken();
    if ($token['location'] == 'board') {
      $data['counter'] = $this->game->board->getMsgCoords($token);
    }

    return $data;
  }

  public function setup()
  {
    $this->getPlayer()->addToken('tokenWind');
    $this->updateUI();
  }

  public function getToken()
  {
    return $this->game->board->getPiecesByType('tokenWind')[0];
  }

  public function getTokenSpace()
  {
    return ['x' => 5, 'y' => 4, 'z' => 1, 'id' => $this->getToken()['id']];
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $center = ['x' => 2, 'y' => 2, 'z' => 0];
    $works = [];

    if ($this->getToken()['location'] == 'board') {
      $works = [$this->getTokenSpace()];
    } else {
      for ($x = 1; $x < 4; $x++) {
        for ($y = 1; $y < 4; $y++) {
          if ($x == $y && $x == 2) {
            continue;
          }
          $space = ['x' => $x + 2 * ($x - 2), 'y' => $y + 2 * ($y - 2), 'z' => 0,];
          $dummy = ['x' => $x, 'y' => $y];
          $space['direction'] = $this->game->board->getDirection($center, $dummy, []);
          $works[] = $space;
        }
      }
    }

    $empty = [
      'id' => 0,
      'playerId' => $this->playerId,
      'works' => $works
    ];
    $arg['workers'] = [$empty];
  }

  public function usePower($action)
  {
    $token = $this->getToken();
    $space = $action[1];

    if ($token['location'] == 'board') {
      // remove token
      $this->removePiece($token);
    } else {
      // place token with the correct direction
      $token['type_arg'] = $space['direction'];
      $space = $this->getTokenSpace();
      $this->placeToken($token, $space);
    }
    $this->updateUI();
  }

  public function stateAfterBuild()
  {
    return 'power';
  }

  public function stateAfterSkipPower()
  {
    return 'endturn';
  }

  public function stateAfterUsePower()
  {
    return 'endturn';
  }

  public function argMove(&$arg)
  {
    $token = $this->getToken();
    if ($token['location'] != "board") {
      return;
    }
    $dir = (($token['direction'] + 3) % 8) + 1; // opposite direction

    Utils::filterWorks($arg,  function ($space, $worker) use ($dir) {
      return $space['direction'] != $dir;
    });
  }

  public function argPlayerMove(&$arg)
  {
    $this->argMove($arg);
  }

  public function argTeammateMove(&$arg)
  {
    $this->argMove($arg);
  }

  public function argOpponentMove(&$arg)
  {
    $this->argMove($arg);
  }
}
