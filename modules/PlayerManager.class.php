<?php

require_once('SantoriniPlayer.class.php');

/*
 * PlayerManager : allows to easily access players, teams, opponents, ...
 *  a player is an instance of SantoriniPlayer class
 */
class PlayerManager extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }

  /*
   * getPlayer : returns the SantoriniPlayer object for the given player ID
   */
  public function getPlayer($playerId = null)
  {
    $playerId = $playerId ?: $this->game->getActivePlayerId();
    $players = $this->getPlayers([$playerId]);
    return $players[0];
  }

  /*
   * getPlayers : Returns array of SantoriniPlayer objects for all/specified player IDs
   */
  public function getPlayers($playerIds = null)
  {
    $sql = "SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_team team, player_no no FROM player";
    if (!empty($playerIds)) {
      $sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
    }
    $rows = self::getObjectListFromDb($sql);

    $players = [];
    foreach ($rows as $row) {
      $player = new SantoriniPlayer($this->game, $row);
      $players[] = $player;
    }
    return $players;
  }

  /*
   * getPlayerCount: return the number of players
   */
  public function getPlayerCount()
  {
    return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM player"));
  }

  /*
   * getUiData : get all ui data of all players : id, no, name, team, color, powers list
   */
  public function getUiData()
  {
    $ui = [];
    foreach ($this->getPlayers() as $player) {
      // TODO associative array? $ui[$player->getId()]
      $ui[] = $player->getUiData();
    }
    return $ui;
  }


  /*
   * getTeammatesIds: return all teammates ids (useful to use within WHERE clause)
   */
  public function getTeammatesIds($pId)
  {
    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_eliminated` = 0 AND `player_team` = ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
    return array_map(function ($player) {
      return $player['id'];
    }, $players);
  }

  /*
   * getTeammates: return all players in the same team as $pId player
   */
  public function getTeammates($pId)
  {
    return $this->getPlayers($this->getTeammatesIds($pId));
  }


  /*
   * getOpponentIds: return all opponnnts ids (useful to use within WHERE clause)
   */
  public function getOpponentsIds($pId)
  {
    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_eliminated` = 0 AND `player_team` <> ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
    return array_map(function ($player) {
      return $player['id'];
    }, $players);
  }

  /*
   * getOpponents: return all players not in the same team as $pId player
   */
  public function getOpponents($pId)
  {
    return $this->getPlayers($this->getOpponentsIds($pId));
  }


  /*
   * isPlayingBefore : check if a player $pId1 is playing before player $pId2
   *    useful to have consistant round number when fetching lastMoves of a player (in Log class)
   */
  public function isPlayingBefore($pId1, $pId2 = null)
  {
    $pId2 = $pId2 ?: $this->game->getActivePlayerId();
    $players = self::getObjectListFromDb("SELECT player_id id, player_no no FROM player WHERE player_id IN ($pId1, $pId2) ORDER BY no");
    return $players[0]['id'] == $pId1;
  }


  /*
  * eliminate : called after a player loose in a 3 players setup
  */
  public function eliminate($pId)
  {
    self::DbQuery("UPDATE piece SET location = 'box' WHERE type IN ('worker') AND player_id = $pId");
    $this->game->eliminatePlayer($pId);
  }
}
