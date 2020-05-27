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
 * states.inc.php
 *
 * santorini game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = array(
  /*
   * BGA framework initial state. Do not modify.
   */
  ST_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => [
      '' => ST_POWERS_SETUP,
    ],
  ],

  /*
   * The God/Hero Powers setup state.
   */
  ST_POWERS_SETUP => [
    'name' => 'powersSetup',
    'description' => '',
    'type' => 'game',
    'action' => 'stPowersSetup',
    'transitions' => [
      'done' => ST_NEXT_PLAYER_PLACE_WORKER,
      'offer' => ST_BUILD_OFFER
    ],
  ],

  ST_BUILD_OFFER => [
    'name' => 'buildOffer',
    'description' => clienttranslate('${actplayer} must offer ${count} powers for selection'),
    'descriptionmyturn' => clienttranslate('${you} must offer ${count} powers for selection'),
    'type' => 'activeplayer',
    'args' => 'argBuildOffer',
    'possibleactions' => ['addOffer', 'removeOffer', 'confirmOffer'],
    'transitions' => [
      'zombiePass' => ST_GAME_END,
      'done' => ST_POWERS_NEXT_PLAYER_CHOOSE,
    ],
  ],

  ST_POWERS_NEXT_PLAYER_CHOOSE => [
    'name' => 'powersNextPlayerChoose',
    'description' => '',
    'type' => 'game',
    'action' => 'stPowersNextPlayerChoose',
    'transitions' => [
      'next' => ST_POWERS_CHOOSE,
      'done' => ST_CHOOSE_FIRST_PLAYER,
    ],
  ],

  ST_POWERS_CHOOSE => [
    'name' => 'powersPlayerChoose',
    'description' => clienttranslate('${actplayer} must choose a power'),
    'descriptionmyturn' => clienttranslate('${you} must choose a power'),
    'type' => 'activeplayer',
    'args' => 'argChoosePower',
    'possibleactions' => ['choosePower'],
    'transitions' => [
      'zombiePass' => ST_GAME_END,
      'done' => ST_POWERS_NEXT_PLAYER_CHOOSE,
    ],
  ],


  ST_CHOOSE_FIRST_PLAYER => [
    'name' => 'chooseFirstPlayer',
    'description' => clienttranslate('${actplayer} must choose who will start'),
    'descriptionmyturn' => clienttranslate('${you} must choose who will start'),
    'type' => 'activeplayer',
    'args' => 'argChooseFirstPlayer',
    'action' => 'stChooseFirstPlayer',
    'possibleactions' => ['chooseFirstPlayer'],
    'transitions' => [
      'zombiePass' => ST_GAME_END,
      'done' => ST_NEXT_PLAYER_PLACE_WORKER,
    ],
  ],


  /*
   * Worker placement
   *  - nextPlayerPlaceWorker : automatically determined the next player who has to place his workers, and if all workers are placed, start the game
   *  - playerPlaceWorker : allow a player to place a worker
   */
  ST_NEXT_PLAYER_PLACE_WORKER => [
    'name' => 'nextPlayerPlaceWorker',
    'description' => '',
    'type' => 'game',
    'action' => 'stNextPlayerPlaceWorker',
    'transitions' => [
      'zombiePass' => ST_NEXT_PLAYER_PLACE_WORKER,
      'next' => ST_PLACE_WORKER,
      'done' => ST_NEXT_PLAYER,
    ],
    'updateGameProgression' => true,
  ],

  ST_PLACE_WORKER => [
    'name' => 'playerPlaceWorker',
    'description' => clienttranslate('${actplayer} must place a worker'),
    'descriptionmyturn' => clienttranslate('${you} must place a worker'),
    'type' => 'activeplayer',
    'args' => 'argPlaceWorker',
    'possibleactions' => ['placeWorker'],
    'transitions' => [
      'zombiePass' => ST_NEXT_PLAYER_PLACE_WORKER,
      'workerPlaced' => ST_NEXT_PLAYER_PLACE_WORKER,
    ],
  ],

  ST_NEXT_PLAYER => [
    'name' => 'nextPlayer',
    'description' => '',
    'type' => 'game',
    'action' => 'stNextPlayer',
    'transitions' => [
      'next' => ST_NEXT_PLAYER,
      'play' => ST_START_OF_TURN,
      'endgame' => ST_GAME_END,
    ],
    'updateGameProgression' => true,
  ],

  ST_START_OF_TURN => [
    'name' => 'startOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stStartOfTurn',
    'transitions' => [
      'move' => ST_MOVE,
      'build' => ST_BUILD,
      'endgame' => ST_GAME_END,
      'power' => ST_USE_POWER,
    ],
  ],

  /*
   * TODO description
   */
  ST_USE_POWER => [
    'name' => 'playerUsePower',
    'description' => clienttranslate('${actplayer} may use its power'),
    'descriptionmyturn' => clienttranslate('${you} may use your power'),
    'type' => 'activeplayer',
    'args' => 'argUsePower',
    'possibleactions' => [ 'use', 'skip' ],
    'transitions' => [
      'next' => ST_NEXT_PLAYER,
      'move' => ST_MOVE,
      'build' => ST_BUILD,
      'endgame' => ST_GAME_END,
    ],
  ],


  ST_MOVE => [
    'name' => 'playerMove',
    'description' => clienttranslate('${actplayer} must move'),
    'descriptionmyturn' => clienttranslate('${you} must move'),
    'type' => 'activeplayer',
    'args' => 'argPlayerMove',
    'action' => 'stBeforeWork',
    'possibleactions' => ['playerMove', 'skip', 'cancel', 'endgame'],
    'transitions' => [
      'zombiePass' => ST_END_OF_TURN,
      'endturn'    => ST_END_OF_TURN,
      'endgame'    => ST_GAME_END,
      'done'       => ST_BUILD,
      'skip'       => ST_BUILD,
      'cancel'     => ST_START_OF_TURN,
      'moveAgain'  => ST_MOVE,
    ],
  ],

  ST_BUILD => [
    'name' => 'playerBuild',
    'description' => clienttranslate('${actplayer} must build'),
    'descriptionmyturn' => clienttranslate('${you} must build'),
    'type' => 'activeplayer',
    'args' => 'argPlayerBuild',
    'action' => 'stBeforeWork',
    'possibleactions' => ['playerBuild', 'skip', 'cancel', 'endgame'],
    'transitions' => [
      'zombiePass' => ST_END_OF_TURN,
      'endturn'    => ST_END_OF_TURN,
      'endgame'    => ST_GAME_END,
      'done'       => ST_END_OF_TURN,
      'skip'       => ST_END_OF_TURN,
      'cancel'     => ST_START_OF_TURN,
      'move'       => ST_MOVE,
      'buildAgain' => ST_BUILD,
    ],
  ],

  ST_END_OF_TURN => [
    'name' => 'endOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndOfTurn',
    'transitions' => [
      'next' => ST_NEXT_PLAYER,
      'endgame' => ST_GAME_END,
    ],
  ],

  /*
   * BGA framework final state. Do not modify.
   */
  ST_GAME_END => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd'
  ]

);
