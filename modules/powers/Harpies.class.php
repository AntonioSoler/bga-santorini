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
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);
    Utils::filterWorkersById($oppWorkers, $worker['id']);
    if (count($oppWorkers) == 0)
      return; // this was not an opponent worker

    // currently, no power affects this series of forces as Tartarus is only a 2-player god
    $space = $work;
    while (true){
      // Must be a free space behind
      $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');
      $newSpace = $this->game->board->getNextSpace($space, $accessibleSpaces);
      if (is_null($newSpace) || $newSpace['z'] > $space['z'])
        return;

      $stats = [[$this->playerId, 'usePower']];
      $space['id'] = $worker['id'];
      $this->game->log->addForce($space, $newSpace, $stats);
      $this->game->board->setPieceAt($space, $newSpace);


      // Notify (same text as Charon to help translators)
      $this->game->notifyAllPlayers('workerMoved', clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name} (${coords})'), [
        'i18n' => ['power_name', 'level_name'],
        'piece' => $space,
        'space' => $newSpace,
        'power_name' => $this->getName(),
        'player_name' => $this->getPlayer()->getName(),
        'player_name2' => $this->game->playerManager->getPlayer($worker['player_id'])->getName(),
        'level_name' => $this->game->levelNames[intval($newSpace['z'])],
        'coords' => $this->game->board->getMsgCoords($space, $newSpace),
      ]);

      $space = $newSpace;
    }
  }
}
