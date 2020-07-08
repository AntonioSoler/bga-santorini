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


$machinestates = [
  /*
   * BGA framework initial state. Do not modify.
   */
  ST_BGA_GAME_SETUP => [
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
      'placeWorker' => ST_NEXT_PLAYER_PLACE_WORKER,
      'offer' => ST_BUILD_OFFER,
      'chooseFirstPlayer' => ST_CHOOSE_FIRST_PLAYER
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
      'done' => ST_CHOOSE_FIRST_PLAYER,
      'goldenFleece' => ST_POWERS_NEXT_PLAYER_CHOOSE,
    ],
  ],

  ST_CHOOSE_FIRST_PLAYER => [
    'name' => 'chooseFirstPlayer',
    'description' => clienttranslate('${actplayer} must choose which power will start (balanced suggestion: ${power_name})'),
    'descriptionmyturn' => clienttranslate('${you} must choose which power will start (balanced suggestion: ${power_name})'),
    'type' => 'activeplayer',
    'args' => 'argChooseFirstPlayer',
    'action' => 'stChooseFirstPlayer',
    'possibleactions' => ['chooseFirstPlayer'],
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
      'done' => ST_NEXT_PLAYER_PLACE_WORKER,
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
      'ram' => ST_PLACE_RAM,
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

  ST_PLACE_RAM => [
    'name' => 'playerPlaceRam',
    'description' => clienttranslate('${actplayer} must place the Ram figure'),
    'descriptionmyturn' => clienttranslate('${you} must place the Ram figure'),
    'type' => 'activeplayer',
    'args' => 'argPlaceRam',
    'possibleactions' => ['placeWorker'],
    'transitions' => [
      'zombiePass' => ST_GAME_END,
      'workerPlaced' => ST_NEXT_PLAYER,
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
      'eliminate' => ST_ELIMINATE_PLAYER,
    ],
    'updateGameProgression' => true,
  ],

  ST_USE_POWER => [
    'name' => 'playerUsePower',
    'description' => clienttranslate('${actplayer} must use ${power_name}\'s power'),
    'descriptionskippable' => clienttranslate('${actplayer} may use ${power_name}\'s power'),
    'descriptionmyturn' => clienttranslate('${you} must use ${power_name}\'s power'),
    'descriptionmyturnskippable' => clienttranslate('${you} may use ${power_name}\'s power'),
    'type' => 'activeplayer',
    'args' => 'argUsePower',
    'possibleactions' => ['use', 'skip', 'cancel'],
    'transitions' => [
      'cancel' => ST_START_OF_TURN,
      'move' => ST_MOVE,
      'build' => ST_BUILD,
      'power' => ST_USE_POWER,
      'endturn' => ST_PRE_END_OF_TURN,
      'endgame' => ST_GAME_END,
      'eliminate' => ST_ELIMINATE_PLAYER,
    ],
  ],

  ST_MOVE => [
    'name' => 'playerMove',
    'description' => clienttranslate('${actplayer} must move'),
    'descriptionskippable' => clienttranslate('${actplayer} may move'),
    'descriptionmyturn' => clienttranslate('${you} must move'),
    'descriptionmyturnskippable' => clienttranslate('${you} may move'),
    'type' => 'activeplayer',
    'args' => 'argPlayerMove',
    'action' => 'stBeforeWork',
    'possibleactions' => ['playerMove', 'skip', 'cancel', 'resign', 'endgame'],
    'transitions' => [
      // Zombie must call power's preEndOfTurn()
      'zombiePass' => ST_PRE_END_OF_TURN,
      'endturn'    => ST_PRE_END_OF_TURN,
      'endgame'    => ST_GAME_END,
      'done'       => ST_BUILD,
      'skip'       => ST_BUILD,
      'cancel'     => ST_START_OF_TURN,
      'moveAgain'  => ST_MOVE,
      'eliminate'  => ST_ELIMINATE_PLAYER,
    ],
  ],

  ST_BUILD => [
    'name' => 'playerBuild',
    'description' => clienttranslate('${actplayer} must build'),
    'descriptionskippable' => clienttranslate('${actplayer} may build'),
    'descriptionmyturn' => clienttranslate('${you} must build'),
    'descriptionmyturnskippable' => clienttranslate('${you} may build'),
    'type' => 'activeplayer',
    'args' => 'argPlayerBuild',
    'action' => 'stBeforeWork',
    'possibleactions' => ['playerBuild', 'skip', 'cancel', 'resign', 'endgame'],
    'transitions' => [
      // Zombie must call power's preEndOfTurn()
      'zombiePass' => ST_PRE_END_OF_TURN,
      'endturn'    => ST_PRE_END_OF_TURN,
      'endgame'    => ST_GAME_END,
      'done'       => ST_PRE_END_OF_TURN,
      'skip'       => ST_PRE_END_OF_TURN,
      'cancel'     => ST_START_OF_TURN,
      'move'       => ST_MOVE,
      'buildAgain' => ST_BUILD,
      'power'      => ST_USE_POWER,
      'eliminate'  => ST_ELIMINATE_PLAYER,
    ],
  ],

  ST_PRE_END_OF_TURN => [
    'name' => 'confirmTurn',
    'description' => clienttranslate('${actplayer} must confirm or restart their turn'),
    'descriptionmyturn' => clienttranslate('${you} must confirm or restart your turn'),
    'type' => 'activeplayer',
    'action' => 'stPreEndOfTurn',
    'possibleactions' => ['confirm', 'cancel'],
    'transitions' => [
      'zombiePass' => ST_END_OF_TURN,
      'endturn'    => ST_END_OF_TURN,
      'confirm'    => ST_END_OF_TURN,
      'cancel'     => ST_START_OF_TURN,
      'eliminate'  => ST_ELIMINATE_PLAYER,
    ],
  ],

  ST_END_OF_TURN => [
    'name' => 'endOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndOfTurn',
    'transitions' => [
      'additionalTurn' => ST_START_OF_TURN,
      'next' => ST_NEXT_PLAYER,
      'endgame' => ST_GAME_END,
    ],
  ],

  ST_ELIMINATE_PLAYER => [
    'name' => 'eliminatePlayer',
    'description' => '',
    'type' => 'game',
    'action' => 'stEliminatePlayer',
    'transitions' => [
      'play' => ST_START_OF_TURN,
      'endgame' => ST_GAME_END,
    ],
  ],


  ST_GAME_END => [
    'name' => 'gameEndStats',
    'description' => '',
    'type' => 'game',
    'action' => 'stGameEndStats',
    'transitions' => [
      'endgame' => ST_BGA_GAME_END,
    ],
  ],

  /*
   * BGA framework final state. Do not modify.
   */
  ST_BGA_GAME_END => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd'
  ]

];
