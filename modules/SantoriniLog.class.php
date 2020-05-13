<?php

// TODO : description
class SantoriniLog extends APP_GameClass
{
  public $game;

  public function __construct($game)
  {
    $this->game = $game;
  }

  public function insert($playerId, $pieceId, $action, $args)
  {
    $playerId = $playerId == -1? $this->game->getActivePlayerId() : $playerId;
    $round = $this->game->getGameStateValue("currentRound");
    $actionArgs = is_array($args)? json_encode($args) : $args;
    self::DbQuery("INSERT INTO log (`round`, `player_id`, `piece_id`, `action`, `action_arg`) VALUES ('$round', '$playerId', '$pieceId', '$action', '$actionArgs')");
  }

  private function addWork($piece, $to, $action)
  {
    $args = [
      'from' => $this->game->board->getCoords($piece),
      'to'   => $this->game->board->getCoords($to),
    ];
    $this->insert(-1, $piece['id'], $action, $args);
  }

  public function addMove($piece, $space)
  {
    $this->addWork($piece, $space, 'move');
  }

  public function addBuild($piece, $space)
  {
    $this->addWork($piece, $space, 'build');
  }


  public function getLastWorks($action, $pId = null, $limit = -1)
  {
    $pId = $pId ?: $this->game->getActivePlayerId();
    $limitClause = ($limit == -1)? '' : "LIMIT $limit";
    $round = $this->game->getGameStateValue("currentRound");
    if(!$this->game->playerManager->isPlayingBefore($pId))
      $round -= 1;
    $works = self::getObjectListFromDb("SELECT * FROM log WHERE `action` = '$action' AND `player_id` = '$pId' AND `round` = $round ORDER BY log_id DESC ".$limitClause);

    return array_map(function($work){
      $args = json_decode($work['action_arg'], true);
      return [
        'pieceId' => $work['piece_id'],
        'from' => $args['from'],
        'to' => $args['to'],
      ];
    }, $works);
  }


  public function getLastMoves($pId = null, $limit = -1)
  {
    return $this->getLastWorks('move', $pId, $limit);
  }

  public function getLastMove($pId = null)
  {
    $moves = $this->getLastMoves($pId, 1);
    return (count($moves) == 1)? $moves[0] : null;
  }

  public function getLastBuilds($pId = null, $limit = -1)
  {
    return $this->getLastWorks('build', $pId, $limit);
  }

  public function getLastBuild($pId = null)
  {
    $builds = $this->getLastBuilds($pId, 1);
    return (count($builds) == 1)? $builds[0] : null;
  }

}
