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
      clienttranslate("[Your Build:] Your Worker may build a block under itself."),
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
      $worker['works'][] = SantoriniBoard::getCoords($worker, 1, true);
    }
  }


  public function playerBuild($worker, $work)
  {
    // If space is free, we can do a classic build -> return false
    $worker2 = $this->game->board->getPiece($work);
    if ($worker2 == null || $worker2['location'] != 'board') {
      return false;
    }

    // Move up the worker
    $space = SantoriniBoard::getCoords($worker);
    $space['z'] = $space['z'] + 1;
    if ($space['z'] > 3) {
      throw new BgaUserException(_("Zeus: This worker would go too high"));
    }
    
    $this->game->removeTokens($work); 
    $this->game->board->setPieceAt($worker, $space);
    $this->game->log->addForce($worker, $space);

    // Build under it
    $stats = [[$this->playerId, 'usePower']];
    $type = 'lvl' . $work['arg'];
    $pieceId = $this->game->board->addPiece([
      'player_id' => $this->playerId,
      'type' => $type,
      'location' => 'board',
      'x' => $work['x'],
      'y' => $work['y'],
      'z' => $work['z'],
    ]);
    $piece = $this->game->board->getPiece($pieceId);
    $this->game->log->addBuild($worker, $work, $stats);

    // Notify
    $this->game->notifyAllPlayers('blockBuiltUnder', clienttranslate('${power_name}: ${player_name} builds a block under themself (${coords})'), [
      'i18n' => ['power_name'],
      'piece' => $piece,
      'under' => $worker,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'coords' => $this->game->board->getMsgCoords($worker),
    ]);

    return true;
  }
}
