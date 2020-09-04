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
    $ids = "('" . implode("','", $this->game->playerManager->getOpponentsIds($this->playerId)) . "')";
    $work = self::getObjectFromDb("SELECT l.* FROM log l LEFT OUTER JOIN piece p ON p.id = l.piece_id WHERE l.`action` = 'move' AND l.`player_id` IN ($ids) AND p.`player_id` IN ($ids) ORDER BY l.log_id DESC LIMIT 1");
    return is_null($work)? null : $work['piece_id'];
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
    if(empty($workers)){
      $this->game->log->addAction("ErisAltTurn");
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
