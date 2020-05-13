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
      'optionPowers' => OPTION_POWERS,
      'optionSetup' => OPTION_SETUP,
      'currentRound' => CURRENT_ROUND,
      'firstPlayer' => FIRST_PLAYER,
    ]);

    // Initialize power deck
    $this->cards = self::getNew('module.common.deck');
    $this->cards->init('card');

    // Initialize logger, board and power manager
    $this->log   = new SantoriniLog($this);
    $this->board = new SantoriniBoard($this);
    $this->powerManager = new PowerManager($this);
    $this->playerManager = new PlayerManager($this);
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
    self::setGameStateInitialValue('currentRound', 0);

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

    // Active first player to play
    $pId = $this->activeNextPlayer();
    self::setGameStateInitialValue('firstPlayer', $pId);
  }

  /*
   * getAllDatas:
   *  Gather all informations about current game situation (visible by the current player).
   *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
   */
  protected function getAllDatas()
  {
    return [
      'fplayers' => $this->playerManager->getUiData(),       // Must not use players as it is already filled by bga
      'placedPieces' => $this->board->getPlacedPieces(),
      'powers' => $this->powers,
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
    return 0.3;
  }


  ///////////////////////////////////////
  //////////   Player actions   /////////
  ///////////////////////////////////////

  /*
   * dividePowers: is called in the fair division setup, after the contestant chose the set of powers
   */
  public function dividePowers($ids)
  {
    self::checkAction('dividePowers');
    $this->powerManager->dividePowers($ids);
    $this->gamestate->nextState('done');
  }

  /*
   * choosePower: is called in the fair division setup, when a player picked a power from the remeaning ones
   */
  public function choosePower($id)
  {
    self::checkAction('choosePower');
    $this->powerManager->choosePower($id);
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
    $pId = self::getActivePlayerId();

    // Get the piece and check owner
    $worker = self::getNonEmptyObjectFromDB("SELECT * FROM piece WHERE id = '$workerId'");
    if($worker['player_id'] != $pId)
      throw new BgaVisibleSystemException('This worker is not yours');

    // Make sure the space is free
    $spaceContent = self::getObjectListFromDb("SELECT * FROM piece WHERE x = '$x' AND y = '$y' AND z = '$z' AND location ='board'");
    if (count($spaceContent) > 0)
      throw new BgaUserException(_("This space is not free"));

    // The worker should be on the ground
    if ($z > 0)
      throw new BgaVisibleSystemException('Worker placed higher than ground floor');

    // Place the worker in this space
    self::DbQuery("UPDATE piece SET location = 'board', x = '$x', y = '$y', z = '$z' WHERE id = '$workerId'");

    // Notify
    $piece = self::getNonEmptyObjectFromDB("SELECT * FROM piece WHERE id = '$workerId'");
    $args = [
      'i18n' => [],
      'piece' => $piece,
      'playerName' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerPlaced', clienttranslate('${playerName} places a worker'), $args);

    $this->gamestate->nextState('workerPlaced');
  }




  /*
   * work: can be either a move or a build (very similar actions)
   *  - int $id : the piece id we want to move
   *  - int $x,$y,$z : the new location on the board
   *  - int actionArg : can hold additional data for the work (e.g. the building type)
   */
  public function work($wId, $x, $y, $z, $actionArg)
  {
    // Get state name to check action
    $state = $this->gamestate->state();
    $stateName = $state['name'];
    self::checkAction($stateName);

    // Get information about the piece and check if work is possible
    $worker = $this->board->getPiece($wId);
    $stateArgs = $state['args']; //();

    $workers = array_values(array_filter($stateArgs['workers'], function($w) use ($worker){
      return $w['id'] == $worker['id'];
    }));
    if(count($workers) != 1)
      throw new BgaUserException(_("This worker can't be used"));

    $works = array_values(array_filter($workers[0]['works'], function($w) use ($x,$y,$z,$actionArg){
      return $w['x'] == $x && $w['y'] == $y && $w['z'] == $z
        && (is_null($actionArg) || in_array($actionArg, $w['arg']) );
    }));
    if (count($works) != 1)
      throw new BgaUserException(_("You cannot reach this space with this worker"));

    // Check if power apply
    $work = ['x' => $x, 'y' => $y, 'z' => $z, 'arg' => $actionArg];
    if ($this->powerManager->$stateName($worker, $work))
      return;

    // Otherwise, do the work
    $this->$stateName($worker, $work);
  }

  /*
   * playerMove: move a worker to a new location on the board
   *  - obj $worker : the piece id we want to move
   *  - obj $space : the new location on the board
   */
  public function playerMove($worker, $space)
  {
    // Move worker
    self::DbQuery("UPDATE piece SET x = {$space['x']}, y = {$space['y']}, z = {$space['z']} WHERE id = {$worker['id']}");
    $this->log->addMove($worker, $space);

    // Notify
    $args = [
      'i18n' => [],
      'piece' => $worker,
      'space' => $space,
      'playerName' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerMoved', clienttranslate('${playerName} moves a worker'), $args);

    // Apply power
    $state = $this->powerManager->stateAfterMove() ?: 'moved';
    $this->gamestate->nextState($state);
  }

  /*
   * playerBuild: build a piece to a location on the board
   *  - obj $worker : the piece id we want to use to build
   *  - obj $space : the location and building type we want to build
   */
  public function playerBuild($worker, $space)
  {
    // Build piece
    $pId = self::getActivePlayerId();
    $type = 'lvl'.$space['arg'];
    self::DbQuery("INSERT INTO piece (`player_id`, `type`, `location`, `x`, `y`, `z`) VALUES ('$pId', '$type', 'board', '{$space['x']}', '{$space['y']}', '{$space['z']}') ");
    $this->log->addBuild($worker, $space);

    // Notify
    $piece = self::getObjectFromDB("SELECT * FROM piece ORDER BY id DESC LIMIT 1");
    $pieceName = ($space['arg'] == 3) ? clienttranslate('dome') : clienttranslate('block');
    $args = [
      'i18n' => ['pieceName'],
      'playerName' => self::getActivePlayerName(),
      'pieceName' => $pieceName,
      'piece' => $piece,
      'level' => $space['z'],
    ];
    $msg = ($space['z'] == 0) ? clienttranslate('${playerName} builds a ${pieceName} at ground level')
      : clienttranslate('${playerName} builds a ${pieceName} at level ${level}');
    self::notifyAllPlayers('blockBuilt', $msg, $args);

    // Apply power
    $state = $this->powerManager->stateAfterBuild() ?: 'built';
    $this->gamestate->nextState($state);
  }


  /*
   * skip: called when a player decide to skip a skippable work
   */
  public function skipWork()
  {
    self::checkAction('skip');

    $args = $this->gamestate->state()['args'];
    if (!$args['skippable'])
      throw new BgaUserException(_("You can't skip this action"));

    // TODO might need to call power to know which is the next state (for move post build for instance)
    $this->gamestate->nextState('skip');
  }


  //////////////////////////////////////////////////
  ////////////   Game state arguments   ////////////
  //////////////////////////////////////////////////

  /*
   * argDividePowers: in the fair division setup, list the possible powers depending on game option
   */
  public function argDividePowers()
  {
    return [
      'count' => self::getPlayerCount(),
      'powers' => $this->powerManager->getPowersInLocation('deck')
    ];
  }

  /*
   * argChoosePower: in the fair division setup, list the remeaing powers for a player to choose
   */
  public function argChoosePower()
  {
    return [
      'powers' => $this->powerManager->getPowersInLocation('stack')
    ];
  }


  /*
   * argPlaceWorker: give the list of accessible unnocupied spaces and the id/type of worker we want to add
   */
  public function argPlaceWorker()
  {
    $pId = self::getActivePlayerId();
    $workers = $this->board->getAvailableWorkers($pId);

    return [
      'worker' => $workers[0],
      'accessibleSpaces' => $this->board->getAccessibleSpaces()
    ];
  }

  /*
   * argPlayerMove: give the list of accessible unnocupied spaces for each worker
   */
  public function argPlayerMove()
  {
    // Return for each worker of this player the spaces he can move to
    $workers = $this->board->getPlacedWorkers(self::getActivePlayerId());
    foreach ($workers as &$worker)
      $worker["works"] = $this->board->getNeighbouringSpaces($worker, 'move');

    $arg = [
      'skippable' => false,
      'verb'    => clienttranslate('must'),
      'workers' => $workers,
    ];

    $this->powerManager->argPlayerMove($arg);
    return $arg;
  }


  /*
   * argPlayerBuild: give the list of accessible unnocupied spaces for the moved worker
   */
  public function argPlayerBuild()
  {
    // Return available spaces neighbouring the moved worker
    $move = $this->log->getLastMove();
    $worker = $this->board->getPiece($move['pieceId']);
    $worker['works'] = $this->board->getNeighbouringSpaces($worker, 'build');

    $arg = [
      'skippable' => false,
      'verb'    => clienttranslate('must'),
      'workers' => [$worker],
    ];

    // Apply power
    $this->powerManager->argPlayerBuild($arg);
    return $arg;
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
    $players = $this->playerManager->getPlayers();
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
    $possiblePowers = $this->powerManager->getPlayablePowers();
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
        $this->powerManager->choosePower(reset($remainingPowers)['id'], $pId);

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
    $workers = $this->board->getAvailableWorkers();
    if (count($workers) == 0) {
      $this->gamestate->nextState('done');
      return;
    }


    // Get unplaced workers for the active player
    $pId = self::getActivePlayerId();
    $workers = $this->board->getAvailableWorkers($pId);
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
    if(self::getGamestateValue("firstPlayer") == $pId){
      $n = (int) self::getGamestateValue('currentRound') + 1;
      self::setGamestateValue("currentRound", $n);
    }
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
