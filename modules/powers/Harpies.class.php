<?php

class Harpies extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = HARPIES;
    $this->name  = clienttranslate('Harpies');
    $this->title = clienttranslate('Winged Menaces');
    $this->text  = [
      clienttranslate("[Opponent's Turn:] Each time an opponent's Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 20;

    $this->implemented = true;
  }

  /* * */

  public function afterOpponentMove($worker, $work)
  {
    // Don't use getPlacedOpponentWorkers() because the power applies to Clio
    $myWorkers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorkersById($myWorkers, $worker['id']);
    if (!empty($myWorkers)) {
      return; // opponent is moving Harpies worker (Dionysus)
    }

    // currently, no power affects this series of forces as Tartarus is only a 2-player god
    $opponent = $this->game->playerManager->getPlayer();
    $space = $this->game->board->getPiece($worker['id']); // vs Charybdis, $worker is not at $work anymore
    $space['direction'] = $work['direction'];
    while (true) {
      // Must be a free space behind
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move', $opponent->getPowerIds());
      $newSpace = $this->game->board->getNextSpace($space, $accessibleSpaces);
      if (is_null($newSpace) || $newSpace['z'] > $space['z']) {
        return;
      }

      // if secret, stop at secret
      if ($worker['location'] == 'secret') {
        $secretWorkers = $this->game->board->getPlacedWorkers($worker['player_id'], true);
        foreach ($secretWorkers as $secretWorker) {
          if ($this->game->board->isSameSpace($secretWorker, $newSpace)) {
            return;
          }
        }
      }

      $stats = [[$this->playerId, 'usePower']];
      $space['id'] = $worker['id'];
      $this->game->log->addForce($space, $newSpace, $stats);
      $this->game->board->setPieceAt($space, $newSpace, $worker['location']);

      // Notify force
      $this->game->notifyWithSecret($worker, 'workerMoved', $this->game->msg['powerForce'], [
        'i18n' => ['power_name', 'level_name'],
        'piece' => $space,
        'space' => $newSpace,
        'power_name' => $this->getName(),
        'player_name' => $this->getPlayer()->getName(),
        'player_name2' => $opponent->getName(),
        'level_name' => $this->game->levelNames[intval($newSpace['z'])],
        'coords' => $this->game->board->getMsgCoords($space, $newSpace),
      ]);

      $space = $newSpace;
    }
  }
}
