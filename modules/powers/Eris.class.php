<?php

class Eris extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ERIS;
    $this->name  = clienttranslate('Eris');
    $this->title = clienttranslate('Goddess of Discord');
    $this->text  = [
      clienttranslate("[Alternative Turn:] Move and build with an opponent Worker that was not the one your opponent most recently moved."),
    ];
    $this->playerCount = [2, 4];
    $this->golden  = true;
    $this->orderAid = 50;

    $this->implemented = true;
  }

  /* * */

  public function getLastOpponentMoveWorkerId()
  {
    $ids = implode(",", $this->game->playerManager->getOpponentsIds($this->playerId));
    // Must compare team (not player ID) to support 4-player games
    $pieceId = self::getUniqueValueFromDB("SELECT l.piece_id FROM log l JOIN player tl ON (tl.player_id = l.player_id) JOIN piece p ON (p.id = l.piece_id) JOIN player tp ON (tp.player_id = p.player_id) WHERE l.action = 'move' AND l.player_id IN ($ids) AND tl.player_team = tp.player_team ORDER BY l.log_id DESC LIMIT 1");
    return $pieceId;
  }

  public function argPlayerMove(&$arg)
  {
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers($this->playerId);
    Utils::filterWorkersById($oppWorkers, $this->getLastOpponentMoveWorkerId(), false);
    $workers = array_merge($workers, $oppWorkers);
    $arg = $this->game->argPlayerWork('move', $workers);
  }


  public function afterPlayerMove($worker, $work)
  {
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorkersById($workers, $worker['id']);
    if (empty($workers)) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction("ErisAltTurn", $stats);
    }
  }


  public function argPlayerBuild(&$arg)
  {
    // Usual turn => usual rule
    if (is_null($this->game->log->getLastAction('ErisAltTurn'))) {
      return;
    }

    $arg = $this->game->argPlayerWork('build', $this->game->board->getPlacedOpponentWorkers());
    $move = $this->game->log->getLastMove();
    if (!is_null($move)) {
      Utils::filterWorkersById($arg, $move['pieceId']);
    }
  }
}
