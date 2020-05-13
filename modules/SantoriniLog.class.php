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

  private function addFromTo($piece, $to, $action)
  {
    $args = [
      'from' => $this->game->board->getCoords($piece),
      'to'   => $this->game->board->getCoords($space),
    ];
    $this->insert(-1, $piece['id'], $action, $args);
  }

  public function addMove($piece, $space)
  {
    $this->addFromTo($piece, $space, 'move');
  }

  public function addBuild($piece, $space)
  {
    $this->addFromTo($piece, $space, 'build');
  }


  public function getLastMoves($pId = null, $limit = -1)
  {
    $pId = $pId ?: $this->game->getActivePlayerId();
    $limitClause = ($limit == -1)? '' : "LIMIT $limit";
    $round = $this->game->getGameStateValue("currentRound");
    if(!$this->game->playerManager->isPlayingBefore($pId))
      $round -= 1;
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


  public function getLastMove($pId = null)
  {
    $moves = $this->getLastMoves($pId, 1);
    if(count($moves) == 1)
      return $moves[0];
    else
      return null;
  }
}
