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
      'optionTeams' => OPTION_TEAMS,
      'firstPlayer' => FIRST_PLAYER,
      'switchPlayer' => SWITCH_PLAYER,
      'switchState' => SWITCH_STATE,
    ]);

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
    // Get player order
    $orderPlayer = []; // [ 0 => playerId =>, 1 => playerId, ... ]
    $orderTable = []; // [ 0 => playerId, 1 => playerId, ... ]
    $i = 0;
    foreach ($players as $pId => $player) {
      $orderPlayer[] = $pId;
      $orderTable[$player['player_table_order']] = $pId;
    }

    if (count($players) == 4) {
      // player_table_order is non-consecutive
      // Rekey $orderTable with 0, 1, 2, 3
      ksort($orderTable);
      $orderTable = array_values($orderTable);

      // Determine the teammate of each player
      $teammateOf = []; // [ playerId => playerId, ... ]
      $optionTeams = intval($this->getGameStateValue('optionTeams'));
      if ($optionTeams == TEAMS_RANDOM) {
        $optionTeams = rand(TEAMS_1_AND_2, TEAMS_1_AND_4);
      }
      if ($optionTeams == TEAMS_1_AND_2) {
        $teammateOf[$orderTable[0]] = $orderTable[1];
        $teammateOf[$orderTable[1]] = $orderTable[0];
        $teammateOf[$orderTable[2]] = $orderTable[3];
        $teammateOf[$orderTable[3]] = $orderTable[2];
      } else if ($optionTeams == TEAMS_1_AND_3) {
        $teammateOf[$orderTable[0]] = $orderTable[2];
        $teammateOf[$orderTable[1]] = $orderTable[3];
        $teammateOf[$orderTable[2]] = $orderTable[0];
        $teammateOf[$orderTable[3]] = $orderTable[1];
      } else if ($optionTeams == TEAMS_1_AND_4) {
        $teammateOf[$orderTable[0]] = $orderTable[3];
        $teammateOf[$orderTable[1]] = $orderTable[2];
        $teammateOf[$orderTable[2]] = $orderTable[1];
        $teammateOf[$orderTable[3]] = $orderTable[0];
      }

      // Preserve BGA player order, only swapping as needed to maintain teams
      if ($orderPlayer[1] == $teammateOf[$orderPlayer[0]]) {
        $orderPlayer = [$orderPlayer[0], $orderPlayer[2], $teammateOf[$orderPlayer[0]], $teammateOf[$orderPlayer[2]]];
      } else {
        $orderPlayer = [$orderPlayer[0], $orderPlayer[1], $teammateOf[$orderPlayer[0]], $teammateOf[$orderPlayer[1]]];
      }
    }

    // Create players
    $gameInfos = self::getGameinfos();
    $values = [];
    $i = 0;
    $nTeams = count($players) == 3 ? 3 : 2;
    foreach ($orderPlayer as $pId) {
      $player = $players[$pId];
      $team = $i++ % $nTeams;
      $color = $gameInfos['player_colors'][$team];
      $values[] = "($pId, '$color', '{$player['player_canal']}', '" . addslashes($player['player_name']) . "', '" . addslashes($player['player_avatar']) . "', $team)";
    }
    self::DbQuery('DELETE FROM player');
    self::DbQuery('INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_team) VALUES ' . implode(',',  $values));
    self::reloadPlayersBasicInfos();

    // Init stats
    $this->log->initStats($players);

    // Create power cards
    $this->powerManager->createCards();

    // Active first player to play
    $pId = $this->activeNextPlayer();
    self::setGameStateInitialValue('firstPlayer', $pId);
    self::setGameStateInitialValue('switchPlayer', 0);
    self::setGameStateInitialValue('switchState', 0);
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
      'placedPieces' => $this->board->getPlacedPieces($this->getCurrentPlayerId()),
      'powers' => $this->powerManager->getUiData($this->getCurrentPlayerId()),
      'goldenFleece' => $this->powerManager->getSpecialPowerId('ram'),
      'nyxNightPower' => $this->powerManager->getSpecialPowerId('nyxNight'),
      'cancelMoveIds' => $this->log->getCancelMoveIds(),
    ];
  }

  /*
   * getGameProgression:
   *  Compute and return the current game progression approximation
   *  This method is called each time we are in a game state with the "updateGameProgression" property set to true
   */
  public function getGameProgression()
  {
    // Each worker and each block = 1%
    // First 11 blocks = extra 5.9%
    // The average game is about 11 blocks (about 80%)
    // BGA allows "concede" after 50%, so after 7 blocks (52% - 56%)
    $workers = $this->board->getWorkerCount();
    $blocks = $this->board->getPieceCount();
    return min($workers + $blocks + (min($blocks, 11) * 5.9), 99);
  }



  /////////////////////////////////////
  /////////////////////////////////////
  //////////    Powers setup   ////////
  /////////////////////////////////////
  /////////////////////////////////////

  /*
   * stPowersSetup:
   *   called right after the board setup, should prepare power deck
   */
  public function stPowersSetup()
  {
    $players = $this->playerManager->getPlayers();
    $nPlayers = count($players);

    // Notify player colors/teams
    if ($nPlayers == 4) {
      self::notifyAllPlayers('message', $this->msg['colorTeam'], [
        'i18n' => ['color'],
        'player_name' => $players[0]->getName(),
        'player_name2' => $players[2]->getName(),
        'color' => $this->colorNames[BLUE],
      ]);
      self::notifyAllPlayers('message', $this->msg['colorTeam'], [
        'i18n' => ['color'],
        'player_name' => $players[1]->getName(),
        'player_name2' => $players[3]->getName(),
        'color' => $this->colorNames[WHITE],
      ]);
    } else {
      self::notifyAllPlayers('message', $this->msg['colorPlayer'], [
        'i18n' => ['color'],
        'player_name' => $players[0]->getName(),
        'color' => $this->colorNames[BLUE],
      ]);
      self::notifyAllPlayers('message', $this->msg['colorPlayer'], [
        'i18n' => ['color'],
        'player_name' => $players[1]->getName(),
        'color' => $this->colorNames[WHITE],
      ]);
      if ($nPlayers == 3) {
        self::notifyAllPlayers('message', $this->msg['colorPlayer'], [
          'i18n' => ['color'],
          'player_name' => $players[2]->getName(),
          'color' => $this->colorNames[PURPLE],
        ]);
      }
    }

    // Create 2 workers for the first player of each team
    foreach ($players as $player) {
      if ($nPlayers == 3 || $player->getNo() <= 2) {
        $player->addWorker('f');
        $player->addWorker('m');
      }
    }

    // Create the Ram figure
    if ($this->powerManager->isGoldenFleece()) {
      self::DbQuery("INSERT INTO piece (`type`, `location`) VALUES ('ram', 'desk')");
    }

    // Prepare a deck with all possible powers for this game (if needed)
    // State transition will be handled within
    $this->powerManager->preparePowers();
  }


  ///////////////////////////////////////
  //////////    Fair division   /////////
  ///////////////////////////////////////
  // The fair division process goes as follows :
  //  - the contestant pick n powers
  //  - contestant choose the first power to place worker
  //  - each player choose one power (contestant is last to choose)
  //////////////////////////////////////

  /*
   * argBuildOffer:
   *   during fair division setup, list the possible powers from the deck
   */
  public function argBuildOffer()
  {
    return [
      'count'  => $this->playerManager->getPlayerCount(),
      'deck'   => $this->powerManager->getPowerIdsInLocation('deck'),
      'offer'  => $this->powerManager->getPowerIdsInLocation('offer'),
      'banned' => $this->powerManager->computeBannedIds(),
    ];
  }

  /*
   * addOffer:
   *   during fair division setup, when contestant adds a power to the offer
   */
  public function addOffer($powerId)
  {
    if (in_array($powerId, $this->powerManager->computeBannedIds())) {
      throw new BgaUserException(_("This power is not compatible with some already selected powers"));
    }

    $this->powerManager->addOffer($powerId);
  }

  /*
   * unselectPower:
   *   during fair division setup, when contestant removes a power from the offer
   */
  public function removeOffer($powerId)
  {
    $this->powerManager->removeOffer($powerId);
  }

  /*
   * confirmOffer:
   *   during fair division setup, when contestant confirms the offer is complete
   */
  public function confirmOffer($autoConfirm = false)
  {
    $n = $this->argBuildOffer()['count'];
    $powers = $this->powerManager->getPowersInLocation('offer');
    if (count($powers) != $n) {
      $msg = sprintf(self::_("You must offer exactly %d powers"), $n);
      throw new BgaUserException($msg);
    }
    usort($powers, ['SantoriniPower', 'compareByName']);

    // Send notification message
    $args = [
      'i18n' => [],
      'player_name' => $autoConfirm ? 'Board Game Arena' : self::getActivePlayerName(),
    ];
    $i = 1;
    $nyx = false;
    foreach ($powers as $power) {
      $argName = "power_name$i";
      $args['i18n'][] = $argName;
      $args[$argName] = $power->getName();
      if ($power->getId() == NYX) {
        $nyx = true;
      }
      $i++;
    }
    self::notifyAllPlayers('buildOffer', $this->msg["offer$n"], $args);

    if ($this->powerManager->isGoldenFleece()) {
      $this->gamestate->nextState('goldenFleece');
    } else if ($nyx) {
      $this->powerManager->prepareNyxNightPowers();
    } else {
      $this->gamestate->nextState('chooseFirstPlayer');
    }
  }

  public function argChooseNyxNightPower()
  {
    return [
      'i18n' => ['power_name', 'special_name'],
      'special_name' => $this->specialNames['nyxNight'],
      'nyxDeck' => $this->powerManager->getPowerIdsInLocation('nyxDeck'),
    ];
  }

  public function chooseNyxNightPower($powerId)
  {
    $this->powerManager->setSpecialPower('nyxNight', $powerId);
    $this->gamestate->nextState('chooseFirstPlayer');
  }

  /*
   * argChooseFirstPlayer: is called in the fair division setup, when the contestant choose who will start
   */
  public function argChooseFirstPlayer($location = 'offer')
  {
    $powers = $this->powerManager->getPowerIdsInLocation($location);
    $suggestion = $this->powerManager->getFirstPlayerSuggestion($powers);
    $arg = [
      'i18n' => ['power_name'],
      'powers' => $powers,
      'power_name' => $this->powerManager->getPower($suggestion)->getName(),
      'suggestion' => $suggestion,
    ];

    // Apply powers (Bia must start)
    $this->powerManager->argChooseFirstPlayer($arg);
    return $arg;
  }

  /*
   * stChooseFirstPlayer: is called before choosing a first player
   *    the only purpose of this function is to automatically chooseFirstPlayer
   *    in case one of the power must play first (eg Bia)
   */
  public function stChooseFirstPlayer()
  {
    $powers = $this->gamestate->state()['args']['powers'];
    if (count($powers) == 1) {
      $this->chooseFirstPlayer($powers[0]);
    }
  }


  /*
   * choosePlayer: is called in the fair division setup, when the contestant pick the first player
   * TODO check if choice is authorized
   */
  public function chooseFirstPlayer($powerId)
  {
    $this->powerManager->setFirstPlayerOffer($powerId);
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

    $remainingPowers = $this->powerManager->getOffer();
    if (count($remainingPowers) > 1) {
      $this->gamestate->nextState('next');
    } else if ($this->powerManager->isGoldenFleece()) {
      $this->gamestate->nextState('done');
    } else {
      $this->choosePower($remainingPowers[0]['id']);
    }
  }

  /*
   * argChoosePower: in the fair division setup, list the remeaing powers for a player to choose
   */
  public function argChoosePower()
  {
    return [
      'offer' => $this->powerManager->getOffer()
    ];
  }

  /*
   * choosePower: is called in the fair division setup, when a player picked a power from the offer
   */
  public function choosePower($powerId)
  {
    if ($this->powerManager->isGoldenFleece()) {
      $this->powerManager->setSpecialPower('ram', $powerId);
    } else {
      $player = $this->playerManager->getPlayer();
      $power = $this->powerManager->getPower($powerId, $player->getId());
      $power = $this->powerManager->addPower($power, 'setup');
    }
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
    // First switch to first player if no worker placed
    $placedWorkers = $this->board->getPlacedWorkers();
    if (count($placedWorkers) == 0) {
      $this->gamestate->changeActivePlayer($this->getGameStateValue('firstPlayer'));
    }

    // Get all remaining workers for all players
    $workers = $this->board->getAvailableWorkers();
    if (count($workers) == 0) {
      if ($this->powerManager->isGoldenFleece()) {
        $this->activeNextPlayer();
        $this->gamestate->nextState('ram');
      } else {
        $this->gamestate->nextState('done');
      }
      return;
    }

    // Get unplaced workers for the active player
    $pId = self::getActivePlayerId();
    $workers = $this->board->getAvailableWorkers($pId);
    if (count($workers) == 0) {
      // No more workers to place => move on to the other player
      $pId = $this->activeNextPlayer();
    }
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

    $arg = [
      'worker' => $workers[0],
      'accessibleSpaces' => $this->board->getAccessibleSpaces()
    ];

    // Apply powers (Bia workers must be placed on perimeter spaces)
    $this->powerManager->argPlaceWorker($arg);
    return $arg;
  }

  /*
   * argPlaceRam: give the list of accessible unnocupied spaces and the id/type of Ram figure we want to add
   */
  public function argPlaceRam()
  {
    $arg = [
      'worker' => $this->board->getRam(),
      'accessibleSpaces' => $this->board->getAccessibleSpaces()
    ];
    return $arg;
  }

  /*
   * placeWorker: place a new worker on the board
   *  - int $id : the piece id we want to move from deck to board
   *  - int $x,$y,$z : the new location on the board
   */
  public function placeWorker($workerId, $x, $y, $z)
  {
    $stateArgs = $this->gamestate->state()['args'];
    if ($stateArgs['worker']['id'] != $workerId) {
      throw new BgaUserException(_('You cannot place this piece'));
    }

    $space = ['x' => $x, 'y' => $y, 'z' => $z, 'arg' => null];
    if (!in_array($space, $stateArgs['accessibleSpaces'])) {
      throw new BgaUserException(_("This space is not available"));
    }

    // Place the worker in this space
    $this->board->setPieceAt($stateArgs['worker'], $space);

    // Notify
    $piece = $this->board->getPiece($workerId);
    self::notifyAllPlayers('workerPlaced', $this->msg['placePiece'], [
      'i18n' => ['piece_name'],
      'piece' => $piece,
      'piece_name' => $this->pieceNames[$piece['type']],
      'player_name' => self::getActivePlayerName(),
      'coords' => $this->board->getMsgCoords($space),
    ]);

    $this->gamestate->nextState('workerPlaced');
  }


  ////////////////////////////////////////////////
  ////////////   Next player / Win   ////////////
  ////////////////////////////////////////////////

  /*
   * stNextPlayer:
   *   go to next player
   */
  public function stNextPlayer($next = true)
  {
    $playerIds = $this->playerManager->getPlayerIds();
    if (count($playerIds) == 1) {
      $this->announceWin($playerIds[0], true);
      return;
    }

    $pId = $next ? $this->activeNextPlayer() : $this->getActivePlayerId();
    if ($this->playerManager->getPlayer($pId)->isEliminated()) {
      $this->stNextPlayer();
      return;
    }
    self::giveExtraTime($pId);

    $this->gamestate->nextState('play');
  }

  /*
   * stStartOfTurn: called at the beggining of each player turn
   */
  public function stStartOfTurn()
  {
    if (!$this->log->isAdditionalTurn()) {
      $this->log->startTurn();
    }

    // Apply power
    $this->powerManager->startOfTurn();
    $state = $this->powerManager->stateStartOfTurn() ?: 'move';
    $this->gamestate->nextState($state);
  }

  /*
   * confirmTurn: called whenever a player confirm their turn
   */
  public function confirmTurn()
  {
    $this->gamestate->nextState('confirm');
  }

  /*
   * stPreEndOfTurn: called at the end of each player turn, before the player confirms
   */
  public function stPreEndOfTurn()
  {
    // Apply power
    $this->powerManager->preEndOfTurn();
  }

  /*
   * stEndOfTurn: called at the end of each player turn, after the player confirms
   */
  public function stEndOfTurn()
  {
    // First check if one player has won
    if ($this->stCheckEndOfGame()) {
      return;
    }

    // Apply power
    $this->powerManager->endOfTurn();
    $state = $this->powerManager->stateEndOfTurn() ?: 'next';
    $this->gamestate->nextState($state);
  }

  /*
   * stCheckEndOfGame:
   *   check if winning condition has been achieved by one of the player
   */
  public function stCheckEndOfGame()
  {
    $work = $this->log->getLastWork();
    $arg = [
      'win' => false,
      'pId' => self::getActivePlayerId(),
      'work' => $work,
    ];

    // Basic rule: Win by moving up to level 3 one of MY workers
    if ($work != null && $work['action'] == 'move') {
      $workers = $this->board->getPlacedWorkers(self::getActivePlayerId());
      Utils::filterWorkersById($workers, $work['pieceId']);
      if (!empty($workers)) {
        $arg['win'] = $work['from']['z'] < $work['to']['z'] && $work['to']['z'] == 3;
      }
    }

    // Apply powers
    $this->powerManager->checkWinning($arg);
    if (array_key_exists('winStats', $arg)) {
      // These stats cannot be reverted
      // e.g., if Hera stops a win and the opponent cancels the turn, keep the stats
      $this->log->incrementStats($arg['winStats']);
    }
    if ($arg['win']) {
      // Still call preEndOfTurn to calculate player statistics
      $this->powerManager->preEndOfTurn();
      $this->announceWin($arg['pId']);
    }
    return $arg['win'];
  }

  /*
   * stGameEndStats:
   *   final state before end of game used to calculate end statistics
   */
  public function stGameEndStats()
  {
    $this->log->gameEndStats();
    $this->gamestate->nextState('endgame');
  }


  /*
   * announceWin: this function is called just before the game ends
   *   the team of player $playerId wins if and only if $win = true (and loose otherwise)
   */
  public function announceWin($playerId, $win = true)
  {
    $players = $win ? $this->playerManager->getTeammates($playerId) : $this->playerManager->getOpponents($playerId);
    if (count($players) == 2) {
      // 4 players
      self::notifyAllPlayers('message', $this->msg['winTeam'], [
        'player_name' => $players[0]->getName(),
        'player_name2' => $players[1]->getName(),
      ]);
    } else {
      // 2 or 3 players
      self::notifyAllPlayers('message', $this->msg['winPlayer'], [
        'player_name' => $players[0]->getName(),
      ]);
    }

    self::DbQuery("UPDATE player SET player_score = 1 WHERE player_team = {$players[0]->getTeam()}");
    $this->gamestate->nextState('endgame');
  }



  /*
   * announceLoose: this function is called when a player cannot move and build during their turn
   */
  public function announceLose($msg = null, $args = null, $pId = null)
  {
    $msg = $msg ?: clienttranslate('${player_name} cannot move/build and is eliminated!');
    if (!is_array($args)) {
      $args = [];
    }
    $args['player_name'] = self::getActivePlayerName();
    self::notifyAllPlayers('message', $msg, $args);

    // Still call preEndOfTurn to calculate player statistics
    $this->powerManager->preEndOfTurn();

    // Announce win or elimination
    $pId = $pId ?: self::getActivePlayerId();
    if ($this->playerManager->getPlayerCount() != 3) {
      // 1v1 or 2v2 => end of the game
      $this->announceWin($pId, false);
    } else {
      // 3 players => eliminate the player
      $playerIds = $this->playerManager->getPlayerIds();
      if (count($playerIds) > 1) {
        if (self::getActivePlayerId() == $pId) {
          $this->gamestate->nextState("eliminate");
        } else {
          $this->playerManager->eliminate($pId);
        }
      } else {
        $this->announceWin($playerIds[0], true);
      }
    }
  }

  /*
   * stEliminatePlayer: this function is called when the active player is eliminated
   */
  public function stEliminatePlayer()
  {
    $pId = $this->getActivePlayerId();
    $this->activeNextPlayer();
    $this->playerManager->eliminate($pId);
    $this->stNextPlayer(false);
  }

  /*
   * stSwitchPlayer: this function is called when we need to change players during the turn (e.g., Gaea)
   */
  public function stSwitchPlayer()
  {
    $switchPlayer = $this->getGameStateValue('switchPlayer');
    $switchState = $this->getGameStateValue('switchState');
    $this->setGameStateValue('switchPlayer', 0);
    $this->setGameStateValue('switchState', 0);

    $this->gamestate->changeActivePlayer($switchPlayer);
    $next = null;
    if ($switchState == ST_USE_POWER) {
      // Gaea is stealing control to place a worker
      $next = 'power';
    } else if ($switchState == ST_BUILD) {
      // Gaea is returning control after opponent build
      $next = $this->powerManager->stateAfterPlayerBuild() ?: 'done';
      // Translate ambiguous state names for "build" context
      if ($next == 'done' || $next == 'skip') {
        $next = 'endturn';
      }
    }
    if ($next == null) {
      throw new BgaVisibleSystemException("stSwitchPlayer: Missing next state (player: $switchPlayer)");
    }
    $this->gamestate->nextState($next);
  }

  /////////////////////////////////////////
  /////////////////////////////////////////
  ///////////    UsePower    //////////////
  /////////////////////////////////////////
  /////////////////////////////////////////

  /*
   * argUsePower: give the list of possible action
   */
  public function argUsePower()
  {
    $arg = [
      'i18n' => ['power_name'],
      'cancelable' => $this->log->canCancelTurn(),
    ];
    $this->powerManager->argUsePower($arg);
    return $arg;
  }

  /*
   * usePower: called when a player decide to use their (non-basic) power
   *
  public function usePower($powerId, $action)
  {
    self::checkAction('use');

    $args = $this->gamestate->state()['args'];
    if ($args['power'] != $powerId || not(in_array($action, $args['actions']))) {
      throw new BgaUserException(_("You can't use this power"));
    }

    // Use power
    $this->powerManager->usePower($powerId, $action);

    $state = $this->powerManager->stateAfterUsePower() ?: 'move';
    $this->gamestate->nextState($state);
  }*/


  /*
   * usePowerWork: called when a player decide to use their (non-basic) power which behave like a work
   */
  public function usePowerWork($powerId, $wId, $x, $y, $z, $actionArg)
  {
    // Check the power and the work
    $args = $this->gamestate->state()['args'];
    if ($args['power'] != $powerId) {
      throw new BgaUserException(_("You can't use this power"));
    }
    $work = Utils::checkWork($args, $wId, $x, $y, $z, $actionArg);

    // Use power
    $this->powerManager->usePower($powerId, [$wId, $work]);
    $stats = [];
    if ($powerId != NEMESIS) {
      $stats = [[self::getActivePlayerId(), 'usePower']];
    }
    $this->log->addAction("usedPower", $stats, [$wId, $work]);

    $state = $this->powerManager->stateAfterUsePower();
    if ($state == null) {
      throw new BgaVisibleSystemException("stateAfterUsePower: Missing next state");
    }
    $this->gamestate->nextState($state);
  }



  /*
   * skip: called when a player decide to skip a skippable work
   */
  public function skipPower()
  {
    $args = $this->gamestate->state()['args'];
    if (!$args['skippable']) {
      throw new BgaUserException(_("You can't skip this action"));
    }
    $this->log->addAction("skippedPower");

    // Apply power
    $state = $this->powerManager->stateAfterSkipPower();
    if ($state == null) {
      throw new BgaVisibleSystemException("stateAfterSkipPower: Missing next state");
    }
    $this->gamestate->nextState($state);
  }



  /////////////////////////////////////////
  /////////////////////////////////////////
  ////////    Work : move / build  ////////
  /////////////////////////////////////////
  /////////////////////////////////////////

  /*
   * argPlayerWork: init the works
   */
  public function argPlayerWork($action, $workers = null)
  {
    $arg = [
      'cancelable' => $this->log->canCancelTurn(),
      'skippable' => false,
      'workers' => $workers ?: $this->board->getPlacedActiveWorkers(),
    ];
    if ($action == 'move') {
      $arg['mayMoveAgain'] = false;
    }
    $player = $this->playerManager->getPlayer();
    $powerIds = $player->getPowerIds();

    foreach ($arg['workers'] as &$worker) {
      $worker["works"] = $this->board->getNeighbouringSpaces($worker, $action, $powerIds);
    }
    Utils::cleanWorkers($arg);

    return $arg;
  }


  /*
   * argPlayerMove: give the list of accessible unnocupied spaces for each worker
   */
  public function argPlayerMove()
  {
    $arg = $this->argPlayerWork('move');
    $this->powerManager->argPlayerMove($arg);
    return $arg;
  }


  /*
   * argPlayerBuild: give the list of accessible unnocupied spaces for builds
   */
  public function argPlayerBuild()
  {
    $arg = $this->argPlayerWork('build');

    // Return available spaces neighbouring the moved worker
    $move = $this->log->getLastMove();
    if (!is_null($move)) {
      Utils::filterWorkersById($arg, $move['pieceId']);
    }

    $this->powerManager->argPlayerBuild($arg);
    return $arg;
  }


  /*
   * stBeforeWork: Check if a work is possible/skippable, otherwise lose
   */
  public function stBeforeWork()
  {
    if ($this->stCheckEndOfGame()) {
      return;
    }
    $state = $this->gamestate->state();
    // TODO: apply power before work ?

    // No move or build => loose unless skippable or cancelable
    if (count($state['args']['workers']) == 0) {
      if ($state['args']['skippable']) {
        $this->skipWork(false);
      } else if (!$state['args']['cancelable']) {
        $this->announceLose();
      }
    }
  }



  /*
   * resign: called when a player decide to resign
   */
  public function resign()
  {
    $this->announceLose($this->msg['resign']);
  }



  /*
   * skip: called when a player decide to skip a skippable work
   */
  public function skipWork($log = true)
  {
    $args = $this->gamestate->state()['args'];
    if (!$args['skippable']) {
      throw new BgaUserException(_("You can't skip this action"));
    }
    if ($log) {
      $this->log->addAction('skippedWork');
    }

    // Apply power
    $state = $this->powerManager->stateAfterSkip() ?: 'skip';
    $this->gamestate->nextState($state);
  }


  /*
   * cancelPreviousWorks: called when a player decide to go back at the beggining of the turn
   */
  public function cancelPreviousWorks()
  {
    if (!$this->log->canCancelTurn()) {
      throw new BgaUserException(_("You have nothing to cancel"));
    }

    // Undo the turn
    $moveIds = $this->log->cancelTurn();
    $playerIds = $this->playerManager->getPlayerIds();
    foreach ($playerIds as $playerId) {
      self::notifyPlayer($playerId, 'cancel', $this->msg['restart'], [
        'placedPieces' => $this->board->getPlacedPieces($playerId),
        'player_name' => self::getActivePlayerName(),
        'moveIds' => $moveIds,
      ]);
    }

    // Apply power
    $this->gamestate->nextState('cancel');
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
    if ($possible) {
      $this->gamestate->checkPossibleAction($stateName);
    } else {
      $this->checkAction($stateName);
    }

    // Check if work is possible
    $stateArgs = $state['args'];
    $work = Utils::checkWork($stateArgs, $wId, $x, $y, $z, $actionArg);

    // Check if power apply
    $worker = $this->board->getPiece($wId);
    if (!$this->powerManager->$stateName($worker, $work)) {
      // Otherwise, do the work
      $this->$stateName($worker, $work);
    }

    // Apply post-work powers
    $nameAfterWork = "after" . ucfirst($stateName);
    $this->powerManager->$nameAfterWork($worker, $work);

    // Apply powers for next state
    $nameNextState = "stateAfter" . ucfirst($stateName);
    $state = $this->powerManager->$nameNextState() ?: 'done';
    $this->gamestate->nextState($state);
  }

  /*
   * playerMove: move a worker to a new location on the board
   *  - obj $worker : the piece id we want to move
   *  - obj $space : the new location on the board
   */
  public function playerMove($worker, $space, $notifyOnly = false)
  {
    if (!$notifyOnly) {
      // Move worker
      $this->board->setPieceAt($worker, $space);
      $this->log->addMove($worker, $space);
    }

    // Notify
    if ($space['z'] > $worker['z']) {
      $msg = $this->msg['moveUp'];
    } else if ($space['z'] < $worker['z']) {
      $msg = $this->msg['moveDown'];
    } else {
      $msg = $this->msg['moveOn'];
    }
    $pId = self::getActivePlayerId();

    self::notifyAllPlayers('workerMoved', $msg, [
      'i18n' => ['level_name'],
      'piece' => $worker,
      'space' => $space,
      'player_name' => self::getActivePlayerName(),
      'level_name' => $this->levelNames[intval($space['z'])],
      'coords' => $this->board->getMsgCoords($worker, $space)
    ]);
  }

  /*
   * playerBuild: build a piece to a location on the board
   *  - obj $worker : the piece id we want to use to build
   *  - obj $space : the location and building type we want to build
   */
  public function playerBuild($worker, $space, $notify = 'blockBuilt')
  {
    // Build piece
    $pId = self::getActivePlayerId();
    $type = 'lvl' . $space['arg'];
    if (!array_key_exists($type, $this->pieceNames)) {
      // What can cause this? https://boardgamearena.com/bug?id=23675
      throw new BgaVisibleSystemException("playerBuild: Invalid piece type: $type");
    }
    $pieceName = $this->pieceNames[$type];
    self::DbQuery("INSERT INTO piece (`player_id`, `type`, `location`, `x`, `y`, `z`) VALUES ('$pId', '$type', 'board', '{$space['x']}', '{$space['y']}', '{$space['z']}') ");
    $this->log->addBuild($worker, $space);

    // Notify
    $piece = self::getObjectFromDB("SELECT * FROM piece ORDER BY id DESC LIMIT 1");
    self::notifyAllPlayers($notify, $this->msg['build'], [
      'i18n' => ['piece_name', 'level_name'],
      'player_name' => self::getActivePlayerName(),
      'piece' => $piece,
      'piece_name' => $pieceName,
      'level_name' => $this->levelNames[intval($space['z'])],
      'coords' => $this->board->getMsgCoords($space),
    ]);
  }


  /*
   * playerKill: kill a piece (only called with specific power eg Medusa, Bia)
   *  - obj $worker : the worker we want to kill
   */
  public function playerKill($worker, $powerName, $incStats = true)
  {
    // Kill worker
    self::DbQuery("UPDATE piece SET location = 'box' WHERE id = {$worker['id']}");
    $stats = $incStats ? [[$this->getActivePlayerId(), 'usePower']] : [];
    $this->log->addRemoval($worker, $stats);

    // Notify
    $this->notifyAllPlayers('pieceRemoved', $this->msg['powerKill'], [
      'i18n' => ['power_name'],
      'piece' => $worker,
      'power_name' => $powerName,
      'player_name' => $this->getActivePlayerName(),
      'player_name2' => $this->playerManager->getPlayer($worker['player_id'])->getName(),
      'coords' => $this->board->getMsgCoords($worker),
    ]);
  }


  /*
   * additionalTurn: grant an additional turn to the player (e.g., Dionysus, Tyche)
   * - obj $power : the power that granted the additional turn
   * - int $n : the additional turn number
   */
  public function additionalTurn($power, $n = 0)
  {
    $player = $power->getPlayer();
    $stats = [[$player->getId(), 'usePower']];
    $this->log->addAction('additionalTurn', $stats, ['power_id' => $power->getId(), 'n' => $n]);
    $this->notifyAllPlayers('message', $this->msg['powerAdditionalTurn'], [
      'i18n' => ['power_name'],
      'power_name' => $power->getName(),
      'player_name' => $player->getName()
    ]);
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
      $this->playerManager->eliminate($activePlayer);
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
    if ($from_version <= 2009010714) {
      self::DbQuery("ALTER TABLE log DROP round");
    }
    if ($from_version <= 2012121801) {
      self::DbQuery("ALTER TABLE piece ADD visibility int(1) NOT NULL DEFAULT 0");
    }
  }


  /////////////////////////////////////////
  /////////////////////////////////////////
  ////////  production bug report  ////////
  /////////////////////////////////////////
  /////////////////////////////////////////

  /*
   * loadBug: in studio, type loadBug(20762) into the table chat to load a bug report from production
   * client side JavaScript will fetch each URL below in sequence, then refresh the page
   */
  public function loadBug($reportId)
  {
    $db = explode('_', self::getUniqueValueFromDB("SELECT SUBSTRING_INDEX(DATABASE(), '_', -2)"));
    $game = $db[0];
    $tableId = $db[1];
    self::notifyAllPlayers('loadBug', "Trying to load <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a>", [
      'urls' => [
        "https://studio.boardgamearena.com/admin/studio/getSavedGameStateFromProduction.html?game=$game&report_id=$reportId&table_id=$tableId",
        "https://studio.boardgamearena.com/table/table/loadSaveState.html?table=$tableId&state=1",
        "https://studio.boardgamearena.com/1/$game/$game/loadBugSQL.html?table=$tableId&report_id=$reportId",
        "https://studio.boardgamearena.com/admin/studio/clearGameserverPhpCache.html?game=$game",
      ]
    ]);
  }

  /*
   * loadBugSQL: in studio, this is one of the URLs triggered by loadBug() above
   */
  public function loadBugSQL($reportId)
  {
    $studioPlayer = self::getCurrentPlayerId();
    $playerIds = $this->playerManager->getPlayerIds();

    $sql = [
      "UPDATE global SET global_value=" . ST_MOVE . " WHERE global_id=1 AND global_value=" . ST_BGA_GAME_END
    ];
    foreach ($playerIds as $pId) {
      $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
      $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";
      $sql[] = "UPDATE card SET card_location_arg=$studioPlayer WHERE card_location_arg=$pId";
      $sql[] = "UPDATE piece SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE log SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE log SET action_arg=REPLACE(action_arg, $pId, $studioPlayer)";
      $studioPlayer++;
    }
    $msg = "<b>Loaded <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a></b><hr><ul><li>" . implode(';</li><li>', $sql) . ';</li></ul>';
    self::warn($msg);
    self::notifyAllPlayers('message', $msg, []);

    foreach ($sql as $q) {
      self::DbQuery($q);
    }
    self::reloadPlayersBasicInfos();
  }

  // call from studio chat to expedite game start
  public function quickBuild()
  {
    $worker = ['id' => 0, 'x' => 0, 'y' => 0, 'z' => 0];

    $this->playerBuild($worker, ['x' => 4, 'y' => 1, 'z' => 0, 'arg' => 0], 'blockBuiltInstant');

    $this->playerBuild($worker, ['x' => 4, 'y' => 2, 'z' => 0, 'arg' => 0], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 4, 'y' => 2, 'z' => 1, 'arg' => 1], 'blockBuiltInstant');

    $this->playerBuild($worker, ['x' => 4, 'y' => 3, 'z' => 0, 'arg' => 0], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 4, 'y' => 3, 'z' => 1, 'arg' => 1], 'blockBuiltInstant');

    $this->playerBuild($worker, ['x' => 3, 'y' => 2, 'z' => 0, 'arg' => 0], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 3, 'y' => 2, 'z' => 1, 'arg' => 1], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 3, 'y' => 2, 'z' => 2, 'arg' => 2], 'blockBuiltInstant');

    $this->playerBuild($worker, ['x' => 2, 'y' => 4, 'z' => 0, 'arg' => 0], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 2, 'y' => 4, 'z' => 1, 'arg' => 1], 'blockBuiltInstant');
    $this->playerBuild($worker, ['x' => 2, 'y' => 4, 'z' => 2, 'arg' => 2]);
  }

  // call from studio chat to copy another studio game's board
  public function copyBoard($tableId)
  {
    self::DbQuery("DELETE FROM piece");
    self::DbQuery("INSERT INTO piece SELECT * FROM ebd_santorini_$tableId.piece");
    self::DbQuery("UPDATE global SET global_value=" . ST_MOVE . " WHERE global_id=1 AND global_value=" . ST_PLACE_WORKER);
  }
}
