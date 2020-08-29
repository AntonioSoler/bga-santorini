<?php

class Gaea extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = GAEA;
    $this->name  = clienttranslate('Gaea');
    $this->title = clienttranslate('Goddess of the Earth');
    $this->text  = [
      clienttranslate("[Setup:] Place 2 extra Workers of your color on your God Power card."),
      clienttranslate("[Any Build:] When a Worker builds a dome, Gaea may immediately place a Worker from her God Power card onto a ground-level space neighboring the dome."),
    ];
    $this->playerCount = [2, 3];
    $this->golden  = false;
    $this->orderAid = 32;

    $this->implemented = true;
  }

  /* * */

  public function getPowerData($filterUsedOrSkipped = true)
  {
    $powerData = $this->game->log->getLastAction('usePowerGaea', $this->playerId, 'build');
    if ($filterUsedOrSkipped) {
      $usedOrSkipped = $this->game->log->getLastActions(['usedPower', 'skippedPower'], $this->playerId, 'usePowerGaea');
      if (count($usedOrSkipped) > 0) {
        return null;
      }
    }
    return $powerData;
  }

  public function getExtraWorkers()
  {
    return $this->game->board->getPiecesByType('worker', null, 'hand');
  }

  public function getUIData()
  {
    $data = parent::getUIData();
    $data['counter'] = $this->playerId != null ? count($this->getExtraWorkers()) : 0;
    return $data;
  }

  public function setup()
  {
    $this->getPlayer()->addWorker('m', 'hand');
    $this->getPlayer()->addWorker('m', 'hand');
    $this->updateUI();
  }

  public function afterPlayerBuild($worker, $work)
  {
    if ($work['arg'] == 3) {
      $workers = $this->getExtraWorkers();
      if (count($workers) > 0) {
        // Find ground-level spaces near the dome
        $spaces = $this->game->board->getNeighbouringSpaces($work, 'build', [GAEA]);
        Utils::filter($spaces, function ($space) {
          return $space['z'] == 0;
        });
        if (count($spaces) > 0) {
          $this->game->log->addAction('usePowerGaea', [], [
            'activePlayerId' => $this->game->getActivePlayerId(),
            'activeWorkerId' => $worker['id'],
            'extraWorkerId' => $workers[0]['id'],
            'works' => $spaces
          ], $this->playerId);
        }
      }
    }
  }

  public function afterOpponentBuild($worker, $work)
  {
    $this->afterPlayerBuild($worker, $work);
  }

  public function stateAfterBuild()
  {
    $powerData = $this->getPowerData();
    if ($powerData == null) {
      return;
    }

    if ($powerData['activePlayerId'] == $this->playerId) {
      // Gaea is already active (Gaea built a dome)
      return 'power';
    } else {
      // Gaea becomes active (opponent built a dome)
      $this->game->setGamestateValue('switchPlayer', $this->playerId);
      $this->game->setGamestateValue('switchState', ST_USE_POWER);
      return 'switch';
    }
  }

  public function stateAfterOpponentBuild()
  {
    return $this->stateAfterBuild();
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $powerData = $this->getPowerData();
    $worker = $this->game->board->getPiece($powerData['activeWorkerId']);
    $worker['works'] = $powerData['works'];
    $arg['workers'] = [$worker];
  }

  public function usePower($action)
  {
    $space = $action[1];
    $powerData = $this->getPowerData();
    $extraWorker = $this->game->board->getPiece($powerData['extraWorkerId']);
    $this->placeWorker($extraWorker, $space);
    $this->updateUI();
  }

  public function stateAfterUsePower()
  {
    $powerData = $this->getPowerData(false);
    if ($powerData['activePlayerId'] == $this->playerId) {
      // Gaea ends turn normally
      return 'endturn';
    } else {
      // Opponent becomes active again
      $this->game->setGamestateValue('switchPlayer', $powerData['activePlayerId']);
      $this->game->setGamestateValue('switchState', ST_BUILD);
      return 'switch';
    }
  }

  public function stateAfterSkipPower()
  {
    return $this->stateAfterUsePower();
  }
}
