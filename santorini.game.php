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

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
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
      'optionPowers' => OPTION_POWERS,
      'optionSetup' => OPTION_SETUP,
    ]);

    // Initialize power deck
    $this->cards = self::getNew('module.common.deck');
    $this->cards->init('card');
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

    // Create players and assign teams
    self::DbQuery('DELETE FROM player');
    $gameInfos = self::getGameinfos();
    $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_team) VALUES ';
    $values = [];
    $i = 0;
    $nTeams = count($players) == 3 ? 3 : 2;
    foreach ($players as $pId => $player) {
      $team = $i++ % $nTeams;
      $color = $gameInfos['player_colors'][$team];
      $values[] = "('" . $pId . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "', '$team')";
    }
    self::DbQuery($sql . implode($values, ','));
    self::reloadPlayersBasicInfos();

    // Create power cards
    $sql = 'INSERT INTO card (card_type, card_type_arg, card_location, card_location_arg) VALUES ';
    $values = [];
    foreach ($this->powers as $powerId => $power) {
      $values[] = "('$powerId', 0, 'box', 0)";
    }
    self::DbQuery($sql . implode($values, ','));
//    $this->cards->createCards($cards, 'box'); TODO : remove ?

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
      // Must not use players as it is already filled by bga
      'fplayers' => array_map(function ($player) {
        return $player->getUiData();
      }, $this->getPlayers()),
      'placedPieces' => $this->getPlacedPieces(),
      'movedWorker' => self::getGamestateValue('movedWorker'),
      'powers' => $this->powers
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
   * getPowersInLocation: return all the power cards in a given location
   */
  public function getPowersInLocation($location)
  {
    $cards = $this->cards->getCardsInLocation($location);
    $powers = array_map(function($card) {
      return $this->powers[$card['type']];
    }, $cards);

    return array_values($powers);
  }


  /*
   * getPower: return the Power object for the active/a specific player
   * TODO : what if several power ?
   */
  public function getPower($pId = null)
  {
    if (empty($pId)) {
      $pId = self::getActivePlayerId();
    }
    $player = self::getPlayer($pId);
    return $player->getPower();
  }

  /*
   * getPlayers: return an array of all SantoriniPlayer objects
   */
  public function getPlayers()
  {
    return SantoriniPlayer::getPlayers($this);
  }

  /*
   * getPlayer: return the SantoriniPlayer object for the active/a specific player
   * params: int $pId -> player id
   */
  public function getPlayer($pId = null)
  {
    if (empty($pId)) {
      $pId = self::getActivePlayerId();
    }
    return SantoriniPlayer::getPlayer($this, $pId);
  }

  /*
   * getPlayerCount: return the number of players
   */
  public function getPlayerCount()
  {
    return self::getUniqueValueFromDB("SELECT COUNT(*) FROM player");
  }

  /*
   * getTeammates: return all players in the same team as $pId player
   */
  public function getTeammates($pId)
  {
    return self::getObjectListFromDb("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_team team, player_no no FROM player
    WHERE `player_team` = ( SELECT `player_team` FROM player WHERE player_id = '$pId')");
  }

  /*
   * getTeammatesIds: return all teammates ids (useful to use within WHERE clause)
   */
  public function getTeammatesIds($pId)
  {
    return array_map(function ($player) {
      return $player['id'];
    }, self::getTeammates($pId));
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
    return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'desk' AND type = 'worker' " . ($pId == -1 ? "" : "AND player_id = '$pId'"));
  }

  /*
   * getPlacedWorkers: return all placed workers
   * opt params : int $pId -> if specified, return only placed workers of corresponding player
   */
  public function getPlacedWorkers($pId = -1)
  {
    $filter = "";
    if ($pId != -1) {
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
  public function getBoard()
  {
    // Create an empty 5*5*4 board
    $board = [];
    for ($x = 0; $x < 5; $x++) {
      $board[$x] = [];
      for ($y = 0; $y < 5; $y++)
        $board[$x][$y] = [];
    }

    // Add all placed pieces
    $pieces = self::getPlacedPieces();
    for ($i = 0; $i < count($pieces); $i++) {
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
    for ($x = 0; $x < 5; $x++)
      for ($y = 0; $y < 5; $y++) {
        $z = 0;
        $blocked = false; // If we see a worker or a dome, the space is not accessible
        // Find next free space above ground
        for (; $z < 4 && !$blocked && array_key_exists($z, $board[$x][$y]); $z++) {
          $p = $board[$x][$y][$z];
          $blocked = ($p['type'] == 'worker' || $p['type'] == 'lvl3');
        }

        if (!$blocked && $z < 4)
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
    $neighbouring = array_filter(self::getAccessibleSpaces(), function ($space) use ($piece, $action) {
      $ok = true;

      // Neighbouring : can't be same place, and should be planar coordinate distant
      $ok = $ok && !($piece['x'] == $space['x'] && $piece['y'] == $space['y']);
      $ok = $ok && abs($piece['x'] - $space['x']) <= 1 && abs($piece['y'] - $space['y']) <= 1;

      // For moving, the new height can't be more than +1
      if ($action == 'moving')
        $ok = $ok && $space['z'] <= $piece['z'] + 1;

      return $ok;
    });

    return array_values($neighbouring);
  }




  /*
   * Get possible powers:
   *   TODO
   * params: TODO
   */
  public function getPlayablePowers()
  {
    $optionPowers = intval(self::getGameStateValue('optionPowers'));
    if ($optionPowers == NONE) {
      return [];
    }

    // Gather information about number of players
    $nPlayers = self::getPlayerCount();

    // Filter powers depending on the number of players and game option
    return array_filter($this->powers, function ($power, $id) use ($nPlayers, $optionPowers) {
      return in_array($nPlayers, $power['players']) &&
        (($optionPowers == SIMPLE && $id <= 10)
          || ($optionPowers == GODS && !$power['hero'])
          || ($optionPowers == HEROES && $power['hero'])
          || ($optionPowers == GODS_AND_HEROES)
          || ($optionPowers == GOLDEN_FLEECE && $power['golden']));
    }, ARRAY_FILTER_USE_BOTH);
  }



  ///////////////////////////////////////
  //////////   Player actions   /////////
  ///////////////////////////////////////
  // Each time a player is doing some game action, one of the methods below is called.
  //   (note: each method below must match an input method in santorini.action.php)
  ///////////////////////////////////////


  /*
   * dividePowers: TODO
   */
  public function dividePowers($ids)
  {
    self::checkAction('dividePowers');

    // Move selected powers to stack
    $this->cards->moveCards($ids, 'stack');

    // Notify other players
    $powers = array_map(function($id){ return $this->powers[$id]['name']; }, $ids);
    $args = [
      'i18n' => [],
      'powers_names' => implode(', ', $powers),
      'player_name' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('powersDivided', clienttranslate('${player_name} selects ${powers_names}'), $args);

    $this->gamestate->nextState('done');
  }


  /*
   * choosePower: TODO
   */
  public function choosePower($id)
  {
    self::checkAction('choosePower');

    // Add choosed power to player
    SantoriniPlayer::getPlayer($this, self::getActivePlayerId())->addPower($id);

    $this->gamestate->nextState('done');
  }


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
      throw new BgaVisibleSystemException('No more workers to place');

    // Make sure the space is free
    $spaceContent = self::getObjectListFromDb("SELECT * FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z' AND location ='board'");
    if (count($spaceContent) > 0)
      throw new BgaUserException(_("This space is not free"));

    // The worker should be on the ground
    if ($z > 0)
      throw new BgaVisibleSystemException('Worker placed higher than ground floor');


    // Place one worker in this space
    self::DbQuery("UPDATE piece SET location = 'board', x = '$x', y = '$y', z = '$z' WHERE id = '$workerId'");

    // Notify
    $piece = self::getObjectFromDB("SELECT * FROM piece WHERE id = '$workerId'");
    $args = [
      'i18n' => [],
      'piece' => $piece,
      'player_name' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerPlaced', clienttranslate('${player_name} places a worker'), $args);

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

    // Check if power apply
    if (self::getPower()->playerMove($wId, $x, $y, $z))
      return;

    // Get information about the piece
    $worker = $this->getPiece($wId);

    // Check if it's belong to active player
    if ($worker['player_id'] != self::getActivePlayerId())
      throw new BgaUserException(_("This worker is not yours"));

    // Check if space is free
    $spaceContent = self::getObjectListFromDB("SELECT id FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z'");
    if (count($spaceContent) > 0)
      throw new BgaUserException(_("This space is not free"));

    // Check if worker can move to this space
    $neighbouring = self::getNeighbouringSpaces($worker, 'move');
    $space = ['x' => $x, 'y' => $y, 'z' => $z];
    if (!in_array($space, $neighbouring))
      throw new BgaUserException(_("You cannot reach this space with this worker"));

    // Move worker
    self::DbQuery("UPDATE piece SET x = '$x', y = '$y', z = '$z' WHERE id = '$wId'");

    // Set moved worker
    self::setGamestateValue('movedWorker', $wId);

    // Notify
    $args = [
      'i18n' => [],
      'piece' => $worker,
      'space' => $space,
      'player_name' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerMoved', clienttranslate('${player_name} moves a worker'), $args);

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
    $wId = self::getGamestateValue('movedWorker');

    // Get information about the piece
    $worker = $this->getPiece($wId);

    // Check if space is free
    $spaceContent = self::getObjectListFromDB("SELECT id FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z'");
    if (count($spaceContent) > 0)
      throw new BgaUserException(_("This space is not free"));

    // Check if worker can move to this space
    $neighbouring = self::getNeighbouringSpaces($worker, 'move');
    $space = ['x' => $x, 'y' => $y, 'z' => $z];
    if (!in_array($space, $neighbouring))
      throw new BgaUserException(_("You cannot build on this space with this worker"));

    // Build piece
    $type = 'lvl' . $z;
    $piece_name = $type == 'lvl3' ? clienttranslate('dome') : clienttranslate('block');
    self::DbQuery("INSERT INTO piece (`player_id`, `type`, `location`, `x`, `y`, `z`) VALUES ('$pId', '$type', 'board', '$x', '$y', '$z') ");

    // Reset moved worker
    self::setGamestateValue('movedWorker', 0);

    // Notify
    $piece = self::getObjectFromDB("SELECT * FROM piece WHERE id = LAST_INSERT_ID()");
    $args = [
      'i18n' => ['piece_name'],
      'player_name' => self::getActivePlayerName(),
      'piece_name' => $piece_name,
      'piece' => $piece,
      'level' => $z,
    ];
    $msg = ($z == 0) ? clienttranslate('${player_name} builds a ${piece_name} at ground level')
      : clienttranslate('${player_name} builds a ${piece_name} at level ${level}');
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
   * argDividePowers: TODO
   */
  public function argDividePowers()
  {
    return [
      'count' => self::getPlayerCount(),
      'powers' => $this->getPowersInLocation('deck')
    ];
  }

  /*
   * argChoosePower: TODO
   */
  public function argChoosePower()
  {
    return [
      'powers' => $this->getPowersInLocation('stack')
    ];
  }



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
    $workers = $this->getPlacedWorkers(self::getActivePlayerId());
    foreach ($workers as &$worker)
      $worker["accessibleSpaces"] = self::getNeighbouringSpaces($worker, 'moving');

    // Apply power
    self::getPower()->argPlayerMove($workers);

    return [
      'verb'    => clienttranslate('must'),
      'workers' => $workers,
    ];
  }


  /*
   * argPlayerBuild: give the list of accessible unnocupied spaces for the moved worker
   */
  public function argPlayerBuild()
  {
    // Return available spaces neighbouring the moved player
    $worker = self::getPiece(self::getGamestateValue('movedWorker'));
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
  public function stPowersSetup()
  {
    // Create 2 workers for the first player of each team
    $players = self::getPlayers();
    $nPlayers = count($players);
    foreach ($players as $player) {
      if ($nPlayers == 3 || $player->getNo() <= 2) {
        $player->addWorker('f');
        $player->addWorker('m');
      }
    }

    // Stop here if not playing with powers
    $optionPowers = intval(self::getGameStateValue('optionPowers'));
    if ($optionPowers == NONE) {
      $this->gamestate->nextState('done');
      return;
    }

    // Make a deck of possible powers
    $possiblePowers = $this->getPlayablePowers();
    $this->cards->moveCards(array_keys($possiblePowers), 'deck');
    $this->cards->shuffle('deck');

    // Go to fair division
    $optionSetup = intval(self::getGameStateValue('optionSetup'));
    if ($optionSetup == FAIR_DIVISION || $optionPowers == GODS_AND_HEROES) {
      $this->gamestate->nextState('divide');
      return;
    }

    // Assign powers randomly
    foreach ($players as $player) {
      // Give the player a random power
      $power = $player->addPower();

      // Remove banned powers
      $this->cards->moveCards($power->getBannedIds(), 'box');

      // Invoke power-specific setup
      $power->setup($player);
    }

    $this->gamestate->nextState('done');
  }


  /*
   * stPowersNextPlayerChoose: TODO
   */
  public function stPowersNextPlayerChoose()
  {
    $pId = $this->activeNextPlayer();

    $remainingPowers = $this->cards->getCardsInLocation('stack');
    if(count($remainingPowers) > 1)
      $this->gamestate->nextState('next');
    else {
      // If only one power left, automatically assign it to the last player
      if(count($remainingPowers) == 1)
        SantoriniPlayer::getPlayer($this, $pId)->addPower(reset($remainingPowers)['id']);

      $this->gamestate->nextState('done');
    }
  }



  /*
   * stNextPlayerPlaceWorker:
   *   if the active player still has no more worker to place, go to next player
   */
  public function stNextPlayerPlaceWorker()
  {
    // Get all the remeaning workers of all players
    $workers = self::getAvailableWorkers();
    if (count($workers) == 0) {
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
