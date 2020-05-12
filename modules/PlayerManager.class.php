<?php

require_once('SantoriniPlayer.class.php');

class PlayerManager extends APP_GameClass
{
  public $game;

  public function __construct($game)
  {
    $this->game = $game;
  }

  /*
   * getPlayers : Returns array of SantoriniPlayer objects for all/specified player IDs
   */
  public function getPlayers($ids = null)
  {
    $players = [];
    $sql = "SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_team team, player_no no FROM player";
    if (!empty($ids)) {
      $sql .= " WHERE player_id IN (" . implode(',', $ids) . ")";
    }
    $rows = self::getObjectListFromDb($sql);

    foreach ($rows as $row) {
      $player = new SantoriniPlayer($this->game, $row);
      $players[] = $player;
    }

    return $players;
  }


  /* Returns the SantoriniPlayer object for the given player ID */
  public function getPlayer($id)
  {
      $players = $this->getPlayers([$id]);
      return $players[0];
  }


  public function getUiData()
  {
    return array_map(function ($player) {
        return $player->getUiData();
    }, $this->getPlayers());
  }


  /*
   * getPlayerCount: return the number of players
   */
  public function getPlayerCount()
  {
    return self::getUniqueValueFromDB("SELECT COUNT(*) FROM player");
  }


  /*
   * getTeammatesIds: return all teammates ids (useful to use within WHERE clause)
   */
  public function getTeammatesIds($pId)
  {
    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_team` = ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
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
    $players = self::getObjectListFromDb("SELECT player_id id FROM player WHERE `player_team` <> ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
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

}
