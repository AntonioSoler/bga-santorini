<?php

class Zeus extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ZEUS;
    $this->name  = clienttranslate('Zeus');
    $this->title = clienttranslate('God of the Sky');
    $this->text  = [
      clienttranslate("[Your Build:] Your Worker may build a block under itself.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 31;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    foreach ($arg['workers'] as &$worker) {
      if ($worker['z'] == 3) {
        continue;
      }

      $space = $this->game->board->getCoords($worker);
      $space['arg'] = [$space['z']];
      $worker['works'][] = $space;
    }
  }


  public function playerBuild($worker, $work)
  {
    // If space is free, we can do a classic build -> return false
    $worker2 = $this->game->board->getPieceAt($work);
    if ($worker2 == null) {
      return false;
    }

    // Move up the worker
    $space = $this->game->board->getCoords($worker);
    $space['z'] = $space['z'] + 1;
    if ($space['z'] > 3) {
      throw new BgaUserException(_("Zeus: This worker would go too high"));
    }
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Build under it
    $pId = $this->game->getActivePlayerId();
    $type = 'lvl' . $work['arg'];
    self::DbQuery("INSERT INTO piece (`player_id`, `type`, `location`, `x`, `y`, `z`) VALUES ('$pId', '$type', 'board', '{$work['x']}', '{$work['y']}', '{$work['z']}') ");
    $this->game->log->addBuild($worker, $work);

    // Notify
    $piece = self::getObjectFromDB("SELECT * FROM piece ORDER BY id DESC LIMIT 1");
    $this->game->notifyAllPlayers('blockBuiltUnder', clienttranslate('${power_name}: ${player_name} builds a block under themself') . $this->board->getMsgCoords($worker), [
      'i18n' => ['power_name'],
      'piece' => $piece,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
    ]);

    return true;
  }
}
