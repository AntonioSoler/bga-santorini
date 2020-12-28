<?php

class Jason extends SantoriniHeroPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = JASON;
    $this->name  = clienttranslate('Jason');
    $this->title = clienttranslate('Leader of the Argonauts');
    $this->text  = [
      clienttranslate("[Setup:] Place an extra Worker of your color on your God Power card."),
      clienttranslate("[Alternative Turn:] [Once], place your extra Worker on an unoccupied ground-level perimeter space. Then move and build with this Worker."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2];
    $this->golden  = false;
    $this->orderAid = 58;

    $this->implemented = true;
  }

  /* * */

  public function getExtraWorker()
  {
    $ids = $this->game->log->getLastAction('extraWorkers', $this->playerId, 'all');
    return $this->game->board->getPiece($ids[0]);
  }

  public function getUiData($playerId)
  {
    $data = parent::getUiData($playerId);
    $data['counter'] = ($this->playerId != null && $this->getExtraWorker()['location'] == 'board') ? 0 : 1;
    return $data;
  }

  public function setup()
  {
    $wId = $this->getPlayer()->addWorker('m', 'hand');
    $this->game->log->addAction('extraWorkers', [], [$wId]);
    $this->updateUI();
  }

  public function stateStartOfTurn()
  {
    return 'power';
  }

  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $worker = $this->getExtraWorker();
    $worker['works'] = $this->game->board->getAccessibleSpaces();
    $arg['workers'] = [$worker];
    Utils::filterWorks($arg, function ($space, $worker) {
      return $space['z'] == 0 && $this->game->board->isPerimeter($space);
    });
  }

  public function usePower($action)
  {
    $worker = $this->getExtraWorker();
    $space = $action[1];
    $this->placeWorker($worker, $space);
    $this->updateUI();
  }

  public function stateAfterSkipPower()
  {
    return 'move';
  }

  public function stateAfterUsePower()
  {
    return 'move';
  }

  public function argPlayerMove(&$arg)
  {
    $worker = $this->getExtraWorker();
    if ($worker['location'] == 'board') {
      Utils::filterWorkersById($arg, $worker['id']);
    }
  }
}
