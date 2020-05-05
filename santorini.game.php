<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * santorini implementation : © Emmanuel Colin <ecolin@boardgamearena.com>
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * santorini.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('constants.inc.php');

class santorini extends Table
{
  public function __construct()
  {
    // Your global variables labels:
    //  Here, you can assign labels to global variables you are using for this game.
    //  You can use any number of global variables with IDs between 10 and 99.
    //  If your game has options (variants), you also have to associate here a label to  the corresponding ID in gameoptions.inc.php.
    // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
    parent::__construct();

    self::initGameStateLabels([
      'movedWorker' => 13,
      'optionGods' => OPTION_GODS,
      'optionHeroes' => OPTION_HEROES,
      'optionSetup' => OPTION_SETUP,
    ]);
  }

  protected function getGameName()
  {
    return 'santorini';
  }

/*
 * setupNewGame:
 *  This method is called only once, when a new game is launched.
 * params:
 *  - array $players
 *  - mixed $players :
 */
protected function setupNewGame($players, $options = array())
{
  self::setGameStateInitialValue('movedWorker', 0);

  // Create players and assign them to teams if necessary
  self::DbQuery('DELETE FROM player');
  $gameInfos = self::getGameinfos();
  $defaultColors = $gameInfos['player_colors'];
  $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_team) VALUES ';
  $values = array();
  $no = 1;
  $nTeams = count($players) == 3 ? 3 : 2;
  foreach ($players as $pId => $player) {
    $team = $no % $nTeams;
    $color = $defaultColors[$team];
    $values[] = "('".$pId."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."', '$team')";
    $no++;
  }
  self::DbQuery($sql . implode($values, ','));

  self::reloadPlayersBasicInfos();

  // Active first player to play
  $this->activeNextPlayer();
}

/*
 * getAllDatas:
 *  Gather all informations about current game situation (visible by the current player).
 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 */
protected function getAllDatas()
{
  return [
    'players' => $this->getPlayers(),
    'placedPieces' => $this->getPlacedPieces(),
    'movedWorker' => self::getGamestateValue('movedWorker'),
  ];
}

/*
 * getGameProgression:
 *  Compute and return the current game progression approximation
 *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
 */
public function getGameProgression()
{
  // TODO
  // Number of pieces on the board / total number of pieces
  $nbr_placed = count(self::getPlacedPieces());

  return 0.3;
//    return $nbr_placed / ($nbr_placed+$nbr_available);
}



////////////////////////////////////////////
//////////// Utility functions ////////////
///////////////////////////////////////////

/*
 * getPlayers: return all basic infos about all players
 */
public function getPlayers()
{
  return self::getCollectionFromDb("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_god god, player_hero hero, player_team team, player_no no FROM player");
}

/*
 * getPlayer: return all basic infos about one player
 * params: int $pId -> player id
 */
public function getPlayer($pId)
{
  return self::getNonEmptyObjectFromDB("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_god god, player_hero hero, player_team team, player_no no FROM player WHERE player_id = $player_id");
}

/*
 * getTeammates: return all players in the same team as $pId player
 */
public function getTeammates($pId)
{
  return self::getCollectionFromDb("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_god god, player_hero hero, player_team team, player_no no FROM player
    WHERE `player_team` = ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
}

/*
 * getTeammatesIds: return all teammates ids (useful to use within WHERE clause)
 */
public function getTeammatesIds($pId)
{
  return array_map(function($player){ return $player['id']; }, self::getTeammates($pId) );
}



/*
 * getPlacedPieces: return all pieces on the board
 */
public function getPlacedPieces()
{
  return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board'");
}


/*
 * getAvailableWorkers: return all available workers
 * opt params : int $pId -> if specified, return only available workers of corresponding player
 */
public function getAvailableWorkers($pId = -1)
{
  return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'desk' AND type = 'worker' ".($pId == -1? "" : "AND player_id = '$pId'") );
}

/*
 * getPlacedWorkers: return all placed workers
 * opt params : int $pId -> if specified, return only placed workers of corresponding player
 */
public function getPlacedWorkers($pId = -1)
{
  $filter = "";
  if($pId != -1){
    $ids = implode(',', self::getTeammatesIds($pId));
    $filter = " AND player_id IN ($ids)";
  }

  return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type = 'worker' $filter");
}


/*
 * getPiece: return all info about a piece
 * params : int $id
 */
public function getPiece($id)
{
  return self::getNonEmptyObjectFromDB("SELECT * FROM piece WHERE id = '$id'");
}



/*
 * getBoard:
 *   return a 3d matrix reprensenting the board with all the placed pieces
 */
public function getBoard(){
  // Create an empty 5*5*4 board
  $board = [];
  for ($x = 0; $x < 5; $x++){
    $board[$x] = [];
    for ($y = 0; $y < 5; $y++)
      $board[$x][$y] = [];
  }

  // Add all placed pieces
  $pieces = self::getPlacedPieces();
  for($i = 0; $i < count($pieces); $i++){
    $p = $pieces[$i];
    $board[$p['x']][$p['y']][$p['z']] = $p;
  }

  return $board;
}


/*
 * getAccessibleSpaces:
 *   return the list of all accessible spaces for either placing a worker, moving or building
 */
public function getAccessibleSpaces()
{
  $board = self::getBoard();

  $accessible = [];
  for($x = 0; $x < 5; $x++)
  for($y = 0; $y < 5; $y++){
    $z = 0;
    $blocked = false; // If we see a worker or a dome, the space is not accessible
    // Find next free space above ground
    for( ; $z < 4 && !$blocked && array_key_exists($z, $board[$x][$y]); $z++){
      $p = $board[$x][$y][$z];
      $blocked = ($p['type'] == 'worker' || $p['type'] == 'lvl3');
    }

    if(!$blocked && $z < 4)
      $accessible[] = [
        'x' => $x,
        'y' => $y,
        'z' => $z,
      ];
  }

  return $accessible;
}


/*
 * getNeighbouringSpaces:
 *   return the list of all accessible neighbouring spaces for either moving a worker or building
 * params:
 *  - mixed $piece : contains all the informations (type, location, player_id) about the piece we use to move/build
 *  - string $action : specifies what kind of action we want to do with this piece (move/build)
 */
public function getNeighbouringSpaces($piece, $action)
{
  // Starting from all accessible spaces, and filtering out those too far or too high (for moving only)
  $neighbouring = array_filter( self::getAccessibleSpaces(), function($space) use ($piece, $action){
    $ok = true;

    // Neighbouring : can't be same place, and should be planar coordinate distant
    $ok = $ok && !($piece['x'] == $space['x'] && $piece['y'] == $space['y']);
    $ok = $ok && abs($piece['x'] - $space['x']) <= 1 && abs($piece['y'] - $space['y']) <= 1;

    // For moving, the new height can't be more than +1
    if($action == 'moving')
      $ok = $ok && $space['z'] <= $piece['z'] + 1;

    return $ok;
  });

  return array_values($neighbouring);
}



/*
 * addWorkers:
 *   TODO
 * params: TODO
 */
public function addWorker($player, $type) {
  $pId = $player['id'];
  $type_arg = $type.$player['team'];
  self::DbQuery("INSERT INTO piece (`player_id`, `type`, `type_arg`, `location`) VALUES ('$pId', 'worker', '$type_arg', 'desk')");
}


///////////////////////////////////////
//////////   Player actions   /////////
///////////////////////////////////////
// Each time a player is doing some game action, one of the methods below is called.
//   (note: each method below must match an input method in santorini.action.php)
///////////////////////////////////////

/*
 * placeWorker: place a new worker on the board
 *  - int $id : the piece id we want to move from deck to board
 *  - int $x,$y,$z : the new location on the board
 */
public function placeWorker($workerId, $x, $y, $z)
{
  self::checkAction('placeWorker');

  // Get unplaced workers of given type for the active player to make sure at least one is remeaning
  $pId = self::getActivePlayerId();
  $workers = self::getAvailableWorkers($pId);
  if (count($workers) == 0)
    throw new BgaVisibleSystemException( 'No more workers to place' );

  // Make sure the space is free
  $spaceContent = self::getObjectListFromDb( "SELECT * FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z' AND location ='board'" );
  if (count($spaceContent) > 0)
    throw new BgaUserException( _("This space is not free") );

  // The worker should be on the ground
  if ($z > 0)
    throw new BgaVisibleSystemException( 'Worker placed higher than ground floor' );


  // Place one worker in this space
  self::DbQuery("UPDATE piece SET location = 'board', x = '$x', y = '$y', z = '$z' WHERE id = '$workerId'");

  // Notify
  $piece = self::getObjectFromDB("SELECT * FROM piece WHERE id = '$workerId'");
  $args = [
    'i18n' => [],
    'piece' => $piece,
    'playerName' => self::getActivePlayerName(),
  ];
  self::notifyAllPlayers('workerPlaced', clienttranslate('${playerName} places a worker'), $args);

  $this->gamestate->nextState('workerPlaced');
}


/*
 * moveWorker: move a worker to a new location on the board
 *  - int $id : the piece id we want to move
 *  - int $x,$y,$z : the new location on the board
 */
public function moveWorker($wId, $x, $y, $z)
{
  self::checkAction('moveWorker');

  // Get information about the piece
  $worker = $this->getPiece($wId);

  // Check if it's belong to active player
  if ($worker['player_id'] != self::getActivePlayerId())
    throw new BgaUserException( _("This worker is not yours") );

  // Check if space is free
  $spaceContent = self::getObjectListFromDB( "SELECT id FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z'" );
  if (count($spaceContent) > 0)
    throw new BgaUserException( _("This space is not free") );

  // Check if worker can move to this space
  $neighbouring = self::getNeighbouringSpaces($worker, 'move');
  $space = [  'x' => $x, 'y' => $y, 'z' => $z ];
  if (!in_array($space, $neighbouring))
    throw new BgaUserException( _("You cannot reach this space with this worker") );

  // Move worker
  self::DbQuery( "UPDATE piece SET x = '$x', y = '$y', z = '$z' WHERE id = '$wId'" );

  // Set moved worker
  self::setGamestateValue( 'movedWorker', $wId );

  // Notify
  $args = [
    'i18n' => [],
    'piece' => $worker,
    'space' => $space,
    'playerName' => self::getActivePlayerName(),
  ];
  self::notifyAllPlayers('workerMoved', clienttranslate('${playerName} moves a worker'), $args);

  $this->gamestate->nextState('moved');
}


/*
 * build: build a piece to a location on the board
 *  - int $x,$y,$z : the location on the board
 */
public function build($x, $y, $z)
{
  self::checkAction('build');

  $pId = self::getActivePlayerId();
  $wId = self::getGamestateValue( 'movedWorker' );

  // Get information about the piece
  $worker = $this->getPiece($wId);

  // Check if space is free
  $spaceContent = self::getObjectListFromDB( "SELECT id FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z'" );
  if (count($spaceContent) > 0)
    throw new BgaUserException( _("This space is not free") );

  // Check if worker can move to this space
  $neighbouring = self::getNeighbouringSpaces($worker, 'move');
  $space = [  'x' => $x, 'y' => $y, 'z' => $z ];
  if (!in_array($space, $neighbouring))
    throw new BgaUserException( _("You cannot build on this space with this worker") );

  // Build block
  $type = 'lvl'.$z;
  self::DbQuery("INSERT INTO piece (`player_id`, `type`, `location`, `x`, `y`, `z`) VALUES ('$pId', '$type', 'board', '$x', '$y', '$z') ");

  // Reset moved worker
  self::setGamestateValue( 'movedWorker', 0 );

  // Notify
  $piece = self::getObjectFromDB("SELECT * FROM piece WHERE id = LAST_INSERT_ID()");
  $args = [
    'i18n' => [],
    'playerName' => self::getActivePlayerName(),
    'piece' => $piece,
    'level' => $z,
  ];
  $msg = ($z == 0) ? clienttranslate('${playerName} builds at ground level')
                   : clienttranslate('${playerName} builds at level ${level}');
  self::notifyAllPlayers('blockBuilt', $msg, $args);

  $this->gamestate->nextState('built');
}


//////////////////////////////////////////////////
////////////   Game state arguments   ////////////
//////////////////////////////////////////////////
// Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
// These methods function is to return some additional information that is specific to the current game state.
//////////////////////////////////////////////////

/*
 * argPlaceWorker: give the list of accessible unnocupied spaces and the id/type of worker we want to add
 */
public function argPlaceWorker()
{
  $pId = self::getActivePlayerId();
  $workers = self::getAvailableWorkers($pId);

  return [
    'worker' => $workers[0],
    'accessibleSpaces' => self::getAccessibleSpaces()
  ];
}

/*
 * argPlayerMove: give the list of accessible unnocupied spaces for each worker
 */
public function argPlayerMove()
{
  // Return for each worker of this player the spaces he can move to
  $workers = $this->getPlacedWorkers( self::getActivePlayerId() );
  foreach ($workers as &$worker)
    $worker["accessibleSpaces"] = self::getNeighbouringSpaces($worker, 'moving');

  return ['workers' => $workers];
}


/*
 * argPlayerBuild: give the list of accessible unnocupied spaces for the moved worker
 */
public function argPlayerBuild()
{
  // Return available spaces neighbouring the moved player
  $worker = self::getPiece(self::getGamestateValue('movedWorker') );
  return [
    'worker' => $worker,
    'accessibleSpaces' => self::getNeighbouringSpaces($worker, 'build')
  ];
}



////////////////////////////////////////////////
////////////   Game state actions   ////////////
////////////////////////////////////////////////
// Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
// The action method of state X is called everytime the current game state is set to X.
////////////////////////////////////////////////

/*
 * stPowersSetup:
 *   called right after the board setup, should give a god/hero to each player unless basic mode
 */
 // TODO when hero is on, we should skip this step ?
public function stGodsSetup()
{
  // Check first if we are playing with powers or not
  $optionGods = intval(self::getGameStateValue('optionGods'));
  if ($optionGods == NO_POWER) {
    $this->gamestate->nextState('done');
    return;
  }

  // Gather information about number of players
  $players = self::getPlayers();
  $nPlayers = count($players);

  // Filter gods depending on the number of players and game option
  $possibleGods = array_filter($this->gods, function($god, $godId) use ($nPlayers, $optionGods) {
    return in_array($nPlayers, $god['players']) &&
      (    ($optionGods == SIMPLE && $godId <= 10)
        || ($optionGods == ADVANCED && !$god['golden'])
        || ($optionGods == GOLDEN_FLEECE)
      );
  }, ARRAY_FILTER_USE_BOTH);

  // Depending on the option setup
  $optionSetup = intval(self::getGameStateValue('optionSetup'));
  if ($optionSetup == DIVIDE_CHOOSE) {
    throw new BgaVisibleSystemException('God Powers: Divide and Choose not yet implemented!');
  }
  else if ($optionSetup == RANDOM) {
    foreach ($players as $pId => $player) {
      $godId = array_rand($possibleGods);
      $godName = $this->gods[$godId]['name'];
      $playerName = $player['name'];
      self::DbQuery("UPDATE player SET player_god = $godId WHERE player_id = $pId");
      self::notifyAllPlayers('message', "Player $playerName assigned god $godName", []);

      // Remove this god and any banned gods
      unset($possibleGods[$godId]);
      foreach ($this->gods[$godId]['banned'] as $bannedId)
        unset($possibleGods[$bannedId]);
    }
  }

  foreach ($players as $player) {
    // Don't create worker for second player of the team
    // TODO make a more robust way to do so ?
    if($player["no"] != $player["team"])
      continue;

    // TODO: some gods grant extra workers
    self::addWorker($player, 'f');
    self::addWorker($player, 'm');
  }

  $this->gamestate->nextState('done');
}


public function stHeroesSetup()
{
  // Check first if we are playing with powers or not
  $optionHeroes = intval(self::getGameStateValue('optionHeroes'));
  if ($optionHeroes == HERO_OFF) {
    $this->gamestate->nextState('done');
    return;
  }

  // Gather information about number of players
  $players = self::getPlayers();
  $nPlayers = count($players);
  $nTeams = ($nPlayers == 3) ? 3 : 2;


  $possibleHeroes = array_filter($this->heroes, function($hero, $heroId) {
    // TODO: filter banned heroes
    return true;
  }, ARRAY_FILTER_USE_BOTH);

  $optionSetup  = intval(self::getGameStateValue('optionSetup'));
  if ($optionSetup == DIVIDE_CHOOSE) {
    throw new BgaVisibleSystemException('Hero Powers: Divide and Choose not yet implemented!');
  }
  else if ($optionSetup == RANDOM){
    foreach ($players as $pId => $player) {
      $heroId = array_rand($possibleHeroes);
      $hreoName = $this->heros[$heroId]['name'];
      $playerName = $player['name'];
      self::DbQuery("UPDATE player SET player_hero = $heroId WHERE player_id = $pId");
      self::notifyAllPlayers('message', "Player $playerName assigned hero $godName", []);

      // Remove this hero and any banned hero
      unset($possibleHeroes[$heroId]);
      foreach ($this->heroes[$heroId]['banned'] as $bannedId)
        unset($possibleHeroes[$bannedId]);
    }
  }

  // TODO: some heroes (JASON) grant extra workers

  $this->gamestate->nextState('done');
}



/*
 * stNextPlayerPlaceWorker:
 *   if the active player still has no more worker to place, go to next player
 */
public function stNextPlayerPlaceWorker()
{
  // Get all the remeaning workers of all players
  $workers = self::getAvailableWorkers();
  if(count($workers) == 0){
    $this->gamestate->nextState('done');
    return;
  }


  // Get unplaced workers for the active player
  $pId = self::getActivePlayerId();
  $workers = self::getAvailableWorkers($pId);
  if (count($workers) == 0)  // No more workers to place => move on to the other player
    $pId = $this->activeNextPlayer();
  self::giveExtraTime($pId);
  $this->gamestate->nextState('next');
}


/*
 * stNextPlayer:
 *   go to next player
 */
public function stNextPlayer()
{
  $pId = $this->activeNextPlayer();
  self::giveExtraTime($pId);
  $this->gamestate->nextState('next');
}


/*
 * stCheckEndOfGame:
 *   check if winning condition has been achieved by one of the player
 */
// TODO: add the losing condition : active player player cannot build
// TODO : the winning condition is not correct : we have to check the level 3 has been achieved by a UP movement during player turn
// (important for some gods that can push players or swap places, ...)
public function stCheckEndOfGame()
{
/*
$player_id = self::getActivePlayerId();
$state=$this->gamestate->state();

// active player has reached level 3 ->  WIN
$positions =  self::getCollectionFromDb('SELECT space_id, x, y, z, piece_id, card_type , card_location_arg FROM board JOIN piece on piece_id=piece.card_id WHERE piece_id is not null AND card_type like "worker%" and z=3');
if ( sizeof( $positions ) > 0 ) {
foreach( $positions as $space_id => $space ) {
self::notifyAllPlayers('message', clienttranslate('A worker reached the top level of a building.'), array());
//var_dump( $space );
self::DbQuery('UPDATE player SET player_score = 1 WHERE player_id = '. $space['card_location_arg'] );
$this->gamestate->nextState('endgame');

}
}

// active player cannot move -> LOOSE
if ($state['name']=='playerMove') {
$workers = $this->pieces->getCardsInLocation('board', self::getActivePlayerId());
$numberElements = 0;
$destinations = array();
foreach ($workers as $worker_id => $worker) {
$destinations[$worker_id] = self::getNeighbouringSpaces($worker_id, true);
$numberElements = $numberElements + sizeof($destinations[$worker_id]);
}
if ( $numberElements == 0 ) {
self::notifyAllPlayers('message', clienttranslate('${player_name} looses the game because none of the workers can move.'), $args);
self::DbQuery('UPDATE player SET player_score = 1 WHERE player_id not in ( '. $player_id .')' );
$this->gamestate->nextState('endgame');
}
}
*/
}

////////////////////////////////////
////////////   Zombie   ////////////
////////////////////////////////////
/*
 * zombieTurn:
 *   This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
 *   You can do whatever you want in order to make sure the turn of this player ends appropriately
 */
public function zombieTurn($state, $activePlayer)
{
  if (array_key_exists('zombiePass', $state['transitions'])) {
    $this->gamestate->nextState('zombiePass');
  } else {
    throw new BgaVisibleSystemException('Zombie player ' . $activePlayer . ' stuck in unexpected state ' . $state['name']);
  }
}

/////////////////////////////////////
//////////   DB upgrade   ///////////
/////////////////////////////////////
// You don't have to care about this until your game has been published on BGA.
// Once your game is on BGA, this method is called everytime the system detects a game running with your old Database scheme.
// In this case, if you change your Database scheme, you just have to apply the needed changes in order to
//   update the game database and allow the game to continue to run with your new version.
/////////////////////////////////////
/*
 * upgradeTableDb
 *  - int $from_version : current version of this game database, in numerical form.
 *      For example, if the game was running with a release of your game named "140430-1345", $from_version is equal to 1404301345
 */
public function upgradeTableDb($from_version)
{
}

}
