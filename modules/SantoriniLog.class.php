<?php

/*
 * SantoriniLog: a class that allows to log some actions
 *   and then fetch these actions latter (useful for powers or rollback)
 */
class SantoriniLog extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }


  ////////////////////////////////
  ////////////////////////////////
  //////////   Adders   //////////
  ////////////////////////////////
  ////////////////////////////////

  /*
   * insert: add a new log entry
   * params:
   *   - $playerId: the player who is making the action
   *   - $pieceId : the piece whose is making the action
   *   - string $action : the name of the action
   *   - array $args : action arguments (eg space)
   */
  public function insert($playerId, $pieceId, $action, $args)
  {
    $playerId = $playerId == -1 ? $this->game->getActivePlayerId() : $playerId;
    $round = $this->game->getGameStateValue("currentRound");
    $actionArgs = is_array($args) ? json_encode($args) : $args;
    self::DbQuery("INSERT INTO log (`round`, `player_id`, `piece_id`, `action`, `action_arg`) VALUES ('$round', '$playerId', '$pieceId', '$action', '$actionArgs')");
  }


  /*
   * starTurn: TODO
   */
  public function startTurn()
  {
    $this->insert(-1, 0, 'startTurn', '{}');
  }

  /*
   * addWork: add a new work entry to log
   */
  private function addWork($piece, $to, $action)
  {
    $args = [
      'from' => $this->game->board->getCoords($piece),
      'to'   => $this->game->board->getCoords($to),
    ];
    $this->insert(-1, $piece['id'], $action, $args);
  }

  /*
   * addMove: add a new move entry to log
   */
  public function addMove($piece, $space)
  {
    $this->addWork($piece, $space, 'move');
  }

  /*
   * addBuild: add a new build entry to log
   */
  public function addBuild($piece, $space)
  {
    $this->addWork($piece, $space, 'build');
  }

  /*
   * addForce: add a new forced move entry to log (eg. Appolo or Minotaur)
   */
  public function addForce($piece, $space)
  {
    $this->addWork($piece, $space, 'force');
  }


  /*
   * addRemoval: add a piece removal entry to log (eg. Bia or Ares)
   */
  public function addRemoval($piece)
  {
    $this->insert(-1, $piece['id'], 'removal', '{}');
  }



  /////////////////////////////////
  /////////////////////////////////
  //////////   Getters   //////////
  /////////////////////////////////
  /////////////////////////////////

  /*
 * getLastWorks: fetch last works of player of current round
 * params:
 *    - string $action : type of work we want to fetch (move/build)
 *    - optionnal int $pId : the player we are interested in, default is active player
 *    - optional int $limit : the number of works we want to fetched (order by most recent first), default is no-limit (-1)
 */
  public function getLastWorks($actions, $pId = null, $limit = -1)
  {
    $pId = $pId ?: $this->game->getActivePlayerId();
    $limitClause = ($limit == -1) ? '' : "LIMIT $limit";
    $actionsNames = "'" . (is_array($actions) ? implode("','", $actions) : $actions) . "'";

    $works = self::getObjectListFromDb("SELECT * FROM log WHERE `action` IN ($actionsNames) AND `player_id` = '$pId' AND `round` = (SELECT round FROM log WHERE `player_id` = $pId AND `action` = 'startTurn' ORDER BY log_id DESC LIMIT 1) ORDER BY log_id DESC " . $limitClause);

    return array_map(function ($work) {
      $args = json_decode($work['action_arg'], true);
      return [
        'action' => $work['action'],
        'pieceId' => $work['piece_id'],
        'from' => $args['from'],
        'to' => $args['to'],
      ];
    }, $works);
  }

  /*
   * getLastWork: fetch the last move/build of player of current round if it exists, null otherwise
   */
  public function getLastWork($pId = null)
  {
    $works = $this->getLastWorks(['move', 'build'], $pId, 1);
    return (count($works) == 1) ? $works[0] : null;
  }


  /*
   * getLastMoves: fetch last moves of player of current round
   */
  public function getLastMoves($pId = null, $limit = -1)
  {
    return $this->getLastWorks('move', $pId, $limit);
  }

  /*
   * getLastMove: fetch the last move of player of current round if it exists, null otherwise
   */
  public function getLastMove($pId = null)
  {
    $moves = $this->getLastMoves($pId, 1);
    return (count($moves) == 1) ? $moves[0] : null;
  }


  /*
   * getLastBuilds: fetch last builds of player of current round
   */
  public function getLastBuilds($pId = null, $limit = -1)
  {
    return $this->getLastWorks('build', $pId, $limit);
  }

  /*
   * getLastBuild: fetch the last build of player of current round if it exists, null otherwise
   */
  public function getLastBuild($pId = null)
  {
    $builds = $this->getLastBuilds($pId, 1);
    return (count($builds) == 1) ? $builds[0] : null;
  }
}
