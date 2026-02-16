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
    
    $hecatePower = null;
    foreach ($this->game->playerManager->getOpponents($this->playerId) as $opponent) {
      foreach ($opponent->getPowers() as $power) {
          if ($power->getId() == HECATE)
              $hecatePower = $power;
      }
    }
    if ($hecatePower == null)
        return;
    
    $spaces = $this->game->board->getAccessibleSpaces('build');
    foreach ($spaces as $space)
    {
        $piece = $this->game->board->getPiecesAt($space, 'dummyHecate')[0];
        $piece['z'] = $space['z'];
        
        $args = [
          'duration' => INSTANT,
          'piece' => $piece,
          'animation' => 'fadeIn',
          'special' => 'dummyHecate',
          'i18n' => ['power_name'],
        ];
        unset($args['animation']);
        $this->game->notifyPlayer($this->playerId, 'workerPlaced', '', $args);        
    }
    
  }

  public function playerMove($worker, $work)
  {
    $hecatePower = null;
    foreach ($this->game->playerManager->getOpponents($this->playerId) as $opponent) {
      foreach ($opponent->getPowers() as $power) {
          if ($power->getId() == HECATE)
              $hecatePower = $power;
      }
    }
    if ($hecatePower == null)
        return false;
    
    for ($x = 0; $x < 5; $x++) {
      for ($y = 0; $y < 5; $y++) {
        $space = ['x' => $x, 'y' => $y, 'arg' => null];
        $piece = $this->game->board->getPiecesAt($space, 'dummyHecate')[0];
        $piece['z'] = $space['z'];
        $args = [
          'duration' => INSTANT,
          'piece' => $piece,
          'animation' => 'fadeIn',
          'special' => 'dummyHecate',
          'i18n' => ['power_name'],
        ];
        unset($args['animation']);
        $this->game->notifyPlayer($this->playerId, 'pieceRemoved', '', $args);        
    }}
    
    if ($worker['player_id'] != $hecatePower->playerId)
      return false;
    
    $args = [
      'duration' => INSTANT,
      'piece' => $worker,
      'animation' => 'fadeIn',
      'special' => 'dummyHecate',
      'i18n' => ['power_name'],
    ];
    unset($args['animation']);
    $this->game->notifyAllPlayers('workerPlaced', '', $args);
    
    $opp = $this->game->board->getPiecesAt($worker, 'secret');
    if (count($opp)>0)
    {
      $opp = $opp[0];
      $this->board->setPieceAt($opp, $work, 'secret');
      $this->log->addMove($opp, $work);
    }
    return ['location' => 'dummyHecate'];
  }

  public function afterPlayerMove($worker, $work)
  {
    $workers = $this->game->board->getPlacedWorkers($this->playerId);
    Utils::filterWorkersById($workers, $worker['id']);
    if (empty($workers)) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction("ErisAltTurn", $stats);
    }
    
    
    $hecatePower = null;
    foreach ($this->game->playerManager->getOpponents($this->playerId) as $opponent) {
      foreach ($opponent->getPowers() as $power) {
          if ($power->getId() == HECATE)
              $hecatePower = $power;
      }
    }
    if ($hecatePower == null)
        return false;
        
    
    if ($worker['player_id'] != $hecatePower->playerId)
      return false;
    
    
    $args = [
      'duration' => INSTANT,
      'piece' => $worker,
      'animation' => 'fadeIn',
      'special' => 'dummyHecate',
      'i18n' => ['power_name'],
    ];
    unset($args['animation']);
    $this->game->notifyAllPlayers('pieceRemoved', '', $args);
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
