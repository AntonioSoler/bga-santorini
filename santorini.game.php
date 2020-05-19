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
require_once('modules/constants.inc.php');

class santorini extends Table
{
  public function __construct()
  {
    parent::__construct();

    // Your global variables labels:
    //  Here, you can assign labels to global variables you are using for this game.
    //  You can use any number of global variables with IDs between 10 and 99.
    //  If your game has options (variants), you also have to associate here a label to  the corresponding ID in gameoptions.inc.php.
    self::initGameStateLabels([
      'optionPowers' => OPTION_POWERS,
      'optionSetup' => OPTION_SETUP,
      'currentRound' => CURRENT_ROUND,
      'firstPlayer' => FIRST_PLAYER,
    ]);

    // Initialize power deck
    $this->cards = self::getNew('module.common.deck');
    $this->cards->init('card');

    // Initialize logger, board, power manager and player manager
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
   *  - mixed $options
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
    $this->powerManager->createCards();

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
      'powers' => $this->powerManager->getUiData(),
    ];
  }

  /*
   * getGameProgression:
   *  Compute and return the current game progression approximation
   *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
   */
  public function getGameProgression()
  {
    return count($this->board->getPlacedPieces()) / 100;
  }



/////////////////////////////////////
/////////////////////////////////////
//////////    Powers setup   ////////
/////////////////////////////////////
/////////////////////////////////////

  /*
   * stPowersSetup:
   *   called right after the board setup, should give a god/hero to each player
   *   unless faire division process (see next block)
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

    // Prepare a deck with all possible powers for this game
    $this->powerManager->preparePowers();

    // In fair division setup player 1 must build the offer
    $optionSetup = intval(self::getGameStateValue('optionSetup'));
    if ($optionSetup == FAIR_DIVISION || $optionPowers == GODS_AND_HEROES) {
      $this->gamestate->nextState('offer');
      return;
    }

    // Assign powers randomly
    foreach ($players as $player) {
      // Give the player a random power and invoke power-specific setup
      $power = $player->addPower();
      $power->setup($player);

      // Remove banned powers
      $this->cards->moveCards($power->getBannedIds(), 'box');
    }

    $this->gamestate->nextState('done');
  }


///////////////////////////////////////
//////////    Fair division   /////////
///////////////////////////////////////
// As stated in the rulebook, the fair division process goes as follows :
//  - the contestant pick n powers
//  - each player choose one power (contestant is last to choose)
//  - contestant choose the first player to place its worker TODO
//////////////////////////////////////

  /*
   * argBuildOffer:
   *   during fair division setup, list the possible powers from the deck
   */
  public function argBuildOffer()
  {
    return [
      'count' => $this->playerManager->getPlayerCount(),
      'deck' => $this->powerManager->getPowerIdsInLocation('deck'),
      'offer' => $this->powerManager->getPowerIdsInLocation('offer'),
    ];
  }

  /*
   * addOffer:
   *   during fair division setup, when player 1 adds a power to the offer
   */
  public function addOffer($powerId) {
    self::checkAction('addOffer');
    $this->powerManager->addOffer($powerId);
  }

  /*
   * unselectPower:
   *   during fair division setup, when player 1 removes a power from the offer
   */
  public function removeOffer($powerId) {
    self::checkAction('removeOffer');
    $this->powerManager->removeOffer($powerId);
  }

  /*
   * confirmOffer:
   *   during fair division setup, when player 1 confirms the offer is complete
   */
  public function confirmOffer()
  {
    self::checkAction('confirmOffer');
    $nPlayers = $this->playerManager->getPlayerCount();
    $powers = $this->powerManager->getPowersInLocation('offer');
    if (count($powers) != $nPlayers) {
      $msg = sprintf( self::_("You must offer exactly %d powers"), $nPlayers );
      throw new BgaUserException($msg);
    }

    // Send notification message
    $msg = clienttranslate('${player_name} offers ${power_name1} and ${power_name2} for selection');
    if ($nPlayers == 3) {
      $msg = clienttranslate('${player_name} offers ${power_name1}, ${power_name2}, and ${power_name3} for selection');
    } else if ($nPlayers == 4) {
      $msg = clienttranslate('${player_name} offers ${power_name1}, ${power_name2}, ${power_name3}, and ${power_name4} for selection');
    }
    $args = [
      'i18n' => [],
      'player_name' => self::getActivePlayerName()
    ];

    $i = 1;
    foreach ($powers as $power) {
      $argName = "power_name$i";
      $args['i18n'][] = $argName;
      $args[$argName] = $power->getName();
      $i++;
    }
    self::notifyAllPlayers('buildOffer', $msg, $args);
    $this->gamestate->nextState('done');
  }


  /*
   * stPowersNextPlayerChoose: is called in the fair division process
   *  - if all player except one already have a power, automatically assign the last one and go on
   *  - otherwise, go to next player and ask him to choose a power
   */
  public function stPowersNextPlayerChoose()
  {
    $pId = $this->activeNextPlayer();

    $remainingPowers = $this->powerManager->getPowerIdsInLocation('offer');
    if(count($remainingPowers) > 1) {
      $this->gamestate->nextState('next');
    } else {
      self::choosePower(reset($remainingPowers));
    }
  }

  /*
   * argChoosePower: in the fair division setup, list the remeaing powers for a player to choose
   */
  public function argChoosePower()
  {
    return [
      'offer' => $this->powerManager->getPowerIdsInLocation('offer')
    ];
  }

  /*
   * choosePower: is called in the fair division setup, when a player picked a power from the remeaning ones
   */
  public function choosePower($powerId)
  {
    $player = $this->playerManager->getPlayer();
    $power = $player->addPower($powerId);
    $power->setup($player);
    $this->gamestate->nextState('done');
  }



///////////////////////////////////////
///////////////////////////////////////
////////    Worker placement   ////////
///////////////////////////////////////
///////////////////////////////////////

  /*
   * stNextPlayerPlaceWorker:
   *   if the active player still has no more worker to place, go to next player
   *   if every player is done with worker placement, start game
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
      'player_name' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerPlaced', clienttranslate('${player_name} places a worker'), $args);

    $this->gamestate->nextState('workerPlaced');
  }


////////////////////////////////////////////////
////////////   Next player / Win   ////////////
////////////////////////////////////////////////

  /*
   * stNextPlayer:
   *   go to next player
   */
  public function stNextPlayer()
  {
    // First check if current player has won
    $this->stCheckEndOfGame();

    // If not, go to next player
    $pId = $this->activeNextPlayer();
    self::giveExtraTime($pId);
    if(self::getGamestateValue("firstPlayer") == $pId){
      $n = (int) self::getGamestateValue('currentRound') + 1;
      self::setGamestateValue("currentRound", $n);
    }
    $this->log->startTurn();

    // Apply power
    $state = $this->powerManager->stateStartTurn() ?: 'move';
    $this->gamestate->nextState($state);
  }


  /*
   * stCheckEndOfGame:
   *   check if winning condition has been achieved by one of the player
   */
  public function stCheckEndOfGame()
  {
    // Basic rule
    $arg = [
      'win' => false,
      'msg' => clienttranslate('${player_name} wins by moving up to level 3!'),
      'pId' => self::getActivePlayerId(),
      'player_name' => self::getActivePlayerName(),
    ];

    $work = $this->log->getLastWork();
    if($work != null && $work['action'] == 'move'){
      $arg['win'] = $work['from']['z'] < $work['to']['z'] && $work['to']['z'] == 3;
    }

    // Apply powers
    $this->powerManager->checkWinning($arg);

    if($arg['win']){
      $player = $this->playerManager->getPlayer($arg['pId']);
      self::notifyAllPlayers('message', $arg['msg'], $arg);
      self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team = {$player->getTeam()}" );
      $this->gamestate->nextState('endgame');
    }

    return $arg['win'];
  }




/////////////////////////////////////////
/////////////////////////////////////////
////////    Work : move / build  ////////
/////////////////////////////////////////
/////////////////////////////////////////

  /*
   * argPlayerMove: give the list of accessible unnocupied spaces for each worker
   */
  public function argPlayerMove()
  {
    // Return for each worker of this player the spaces he can move to
    $workers = $this->board->getPlacedActiveWorkers();
    foreach ($workers as &$worker)
      $worker["works"] = $this->board->getNeighbouringSpaces($worker, 'move');

    $arg = [
      'skippable' => false,
      'workers' => $workers,
    ];

    $this->powerManager->argPlayerMove($arg);
    Utils::cleanWorkers($arg);

    $playerName = self::getActivePlayerName();
    if($arg['skippable']){
        $arg['description'] = clienttranslate("${playerName} may move a worker");
        $arg['descriptionmyturn'] = clienttranslate('You may move a worker');
    }
    else {
        $arg['description'] = clienttranslate("${playerName} must move a worker");
        $arg['descriptionmyturn'] = clienttranslate('You must move a worker');
    }

    return $arg;
  }


  /*
   * argPlayerBuild: give the list of accessible unnocupied spaces for builds
   */
  public function argPlayerBuild()
  {
    $arg = [
      'skippable' => false,
      'workers' => [],
    ];

    // Return available spaces neighbouring the moved worker
    $move = $this->log->getLastMove();
    if(!is_null($move)){
      $worker = $this->board->getPiece($move['pieceId']);
      $worker['works'] = $this->board->getNeighbouringSpaces($worker, 'build');
      $arg['workers'][] = $worker;
    }

    // Apply power
    $this->powerManager->argPlayerBuild($arg);
    Utils::cleanWorkers($arg);

    $playerName = self::getActivePlayerName();
    if($arg['skippable']){
        $arg['description'] = clienttranslate("${playerName} may build");
        $arg['descriptionmyturn'] = clienttranslate('You may build');
    }
    else {
        $arg['description'] = clienttranslate("${playerName} must build");
        $arg['descriptionmyturn'] = clienttranslate('You must build');
    }

    return $arg;
  }


  /*
   * stBeforeWork: Check if a work is possible/skippable, otherwise loose
   */
  public function stBeforeWork()
  {
    if($this->stCheckEndOfGame())
      return;

    $state = $this->gamestate->state();
    // TODO: apply power before work ?

    if(count($state['args']['workers']) == 0){
      // No move or build => loose unless skippable
      if($state['args']['skippable']){
        $this->skipWork();
        return;
      }

      // Notify
      $pId = self::getActivePlayerId();
      $args = [
        'i18n' => [],
        'player_name' => self::getActivePlayerName(),
      ];
      self::notifyAllPlayers('message', clienttranslate('${player_name} cannot move/build and is eliminated!'), $args);

      // 1v1 or 2v2 => end of the game
      if($this->playerManager->getPlayerCount() != 3){
        $player = $this->playerManager->getPlayer($pId);
        self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team != {$player->getTeam()}");
        $this->gamestate->nextState('endgame');
      }
      // 3 players => eliminate the player
      else {
        $this->playerManager->eliminate($pId);
        $this->gamestate->nextState('next');
      }
    }
    // Only one work possible => do it but notify player first
    else if(count($state['args']['workers']) == 1 && !$state['args']['skippable']){
      $worker = $state['args']['workers'][0];
      if(count($worker['works']) > 1)
        return;
      $work = $worker['works'][0];
      if(is_array($work['arg']) && count($work['arg']) > 1)
        return;
      $arg = is_array($work['arg'])? $work['arg'][0] : $work['arg'];

      self::notifyPlayer(self::getActivePlayerId(), 'automatic', clienttranslate('Next action will be done automatically since it\'s the only one available'), []);
      $this->work($worker['id'], $work['x'], $work['y'], $work['z'], $arg, true);
    }
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

    // Apply power
    $state = $this->powerManager->stateAfterSkip() ?: 'skip';
    $this->gamestate->nextState($state);
  }


  /*
   * work: can be either a move or a build (very similar actions)
   *  - int $id : the piece id we want to move
   *  - int $x,$y,$z : the new location on the board
   *  - int actionArg : can hold additional data for the work (e.g. the building type)
   */
  public function work($wId, $x, $y, $z, $actionArg, $possible = false)
  {
    // Get state name to check action
    $state = $this->gamestate->state();
    $stateName = $state['name'];
    if($possible)
      $this->gamestate->checkPossibleAction($stateName);
    else
      $this->checkAction($stateName);

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
    if (!$this->powerManager->$stateName($worker, $work)){
      // Otherwise, do the work
      $this->$stateName($worker, $work);
    }

    // Apply post-work powers
    $nameAfterWork = "after". ucfirst($stateName);
    $this->powerManager->$nameAfterWork($worker, $work);

    // Apply powers for next state
    $nameNextState = "stateAfter". ucfirst($stateName);
    $state = $this->powerManager->$nameNextState() ?: 'done';
    $this->gamestate->nextState($state);
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
      'player_name' => self::getActivePlayerName(),
    ];
    self::notifyAllPlayers('workerMoved', clienttranslate('${player_name} moves a worker'), $args);
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
      'player_name' => self::getActivePlayerName(),
      'piece_name' => $pieceName,
      'piece' => $piece,
      'level' => $space['z'],
    ];
    $msg = ($space['z'] == 0) ? clienttranslate('${player_name} builds a ${piece_name} at ground level')
      : clienttranslate('${player_name} builds a ${piece_name} at level ${level}');
    self::notifyAllPlayers('blockBuilt', $msg, $args);
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
      $this->playerManager->eliminate($activePlayer);
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
