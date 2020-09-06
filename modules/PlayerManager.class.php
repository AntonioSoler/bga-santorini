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
    if (is_array($playerIds)) {
      $sql .= " WHERE player_id IN ('" . implode("','", $playerIds) . "')";
    }
    $sql .= " ORDER BY no";
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
   * getPlayingPlayers: return non-eliminated players
   */
  public function getRemeaningPlayersIds()
  {
    return self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_eliminated` = 0 AND `player_zombie` = 0");
  }

  /*
   * getTeams: return the team IDs that have any active players
   */
  public function getTeams()
  {
    return self::getObjectListFromDb("SELECT DISTINCT player_team FROM player WHERE player_eliminated = 0 AND player_zombie = 0", true);
  }



  /*
   * getUiData : get all ui data of all players : id, no, name, team, color, powers list
   */
  public function getUiData()
  {
    $ui = [];
    foreach ($this->getPlayers() as $player) {
      $ui[] = $player->getUiData();
    }
    return $ui;
  }


  /*
   * getTeammatesIds: return all teammates ids (useful to use within WHERE clause)
   */
  public function getTeammatesIds($pId = -1, $excludeSelf = false)
  {
    if ($pId == -1 || $pId == null) {
      $pId = $this->game->getActivePlayerId();
    }

    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE player_eliminated = 0 AND player_team = (SELECT player_team FROM player WHERE player_id = $pId)" . ($excludeSelf ? " AND player_id != $pId" : ''));
    return array_map(function ($player) {
      return $player['id'];
    }, $players);
  }

  /*
   * getTeammates: return all players in the same team as $pId player
   */
  public function getTeammates($pId, $excludeSelf = false)
  {
    return $this->getPlayers($this->getTeammatesIds($pId, $excludeSelf));
  }


  /*
   * getOpponentIds: return all opponnnts ids (useful to use within WHERE clause)
   */
  public function getOpponentsIds($pId = -1)
  {
    if ($pId == -1 || $pId == null) {
      $pId = $this->game->getActivePlayerId();
    }

    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_eliminated` = 0 AND `player_team` <> ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
    return array_map(function ($player) {
      return $player['id'];
    }, $players);
  }

  /*
   * getOpponents: return all players not in the same team as $pId player
   */
  public function getOpponents($pId = -1)
  {
    return $this->getPlayers($this->getOpponentsIds($pId));
  }


  /*
  * eliminate : called after a player meets any losing condition
  */
  public function eliminate($pId)
  {
    foreach ($this->game->board->getPlacedWorkers($pId) as $worker) {
      self::DbQuery("UPDATE piece SET location = 'box' WHERE id = {$worker['id']}");
      $this->game->notifyAllPlayers('pieceRemovedInstant', '', ['piece' => $worker]);
    }
    $this->game->eliminatePlayer($pId);
  }
}
