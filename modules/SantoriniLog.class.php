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

  public function addMove($piece, $space)
  {
    $args = [
      'from' => ['x' => (int) $piece['x'], 'y' => (int) $piece['y'], 'z' => (int) $piece['z']],
      'to'   => ['x' => (int) $space['x'], 'y' => (int) $space['y'], 'z' => (int) $space['z']],
    ];
    $this->insert(-1, $piece['id'], 'move', $args);
  }


  public function getLastMoves($limit = -1, $pId = -1)
  {
    $pId = ($pId == -1)? $this->game->getActivePlayerId() : $pId;
    $limitClause = ($limit == -1)? '' : "LIMIT $limit";
    $round = $this->game->getGameStateValue("currentRound");
    $rawMoves = self::getObjectListFromDb("SELECT * FROM log WHERE `action` = 'move' AND `player_id` = '$pId' AND `round` = $round ORDER BY log_id DESC ".$limitClause);

    $moves = array_map(function($move){
      $args = json_decode($move['action_arg'], true);
      return [
        'pieceId' => $move['piece_id'],
        'from' => $args['from'],
        'to' => $args['to'],
      ];
    }, $rawMoves);

    return $moves;
  }


  public function getLastMove($pId = -1)
  {
    $moves = $this->getLastMoves(1, $pId);
    if(count($moves) == 1)
      return $moves[0];
    else
      return null;
  }
}
