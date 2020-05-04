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
 * The initial state.
 * Please do not modify.
 */
1 => [
  'name' => 'gameSetup',
  'description' => '',
  'type' => 'manager',
  'action' => 'stGameSetup',
  'transitions' => [
    '' => 2,
  ],
],

/*
 * The gods setup state.
 * TODO: game state for the moment, but depending on game mode, might be different
 */
2 => [
  'name' => 'godsSetup',
  'description' => '',
  'type' => 'game',
  'action' => 'stGodsSetup',
  'transitions' => [
    'done' => 3,
  ],
],


/*
 * Worker placement
 *  - nextPlayerPlaceWorker : automatically determined the next player who has to place his workers, and if all workers are placed, start the game
 *  - playerPlaceWorker : allow a player to place a worker
 */
3 => [
  'name' => 'nextPlayerPlaceWorker',
  'description' => '',
  'type' => 'game',
  'action' => 'stNextPlayerPlaceWorker',
  'transitions' => [
    'next' => 4,
    'done' => 6,
  ],
  'updateGameProgression' => true,
],

4 => [
  'name' => 'playerPlaceWorker',
  'description' => clienttranslate('${actplayer} must place a worker'),
  'descriptionmyturn' => clienttranslate('${you} must place a worker'),
  'type' => 'activeplayer',
  'args' => 'argPlaceWorker',
  'possibleactions' => ['placeWorker'],
  'transitions' => [
    'zombiePass' => 3,
    'workerPlaced' => 3,
  ],
],


/*
 * TODO description
 */
5 => [
  'name' => 'nextPlayer',
  'description' => '',
  'type' => 'game',
  'action' => 'stNextPlayer',
  'transitions' => [
    'next' => 6
  ],
  'updateGameProgression' => true,
],


/*
 * Worker move TODO description
 */
6 => [
  'name' => 'playerMove',
  'description' => clienttranslate('${actplayer} must move a worker'),
  'descriptionmyturn' => clienttranslate('${you} must move a worker'),
  'type' => 'activeplayer',
  'args' => 'argPlayerMove',
  'action' => 'stCheckEndOfGame',
  'possibleactions' => [ 'moveWorker', 'endgame' ],
  'transitions' => [
    'zombiePass' => 5,
    'moved' => 7,
    'endgame' => 99,
  ],
],

/*
 * Build TODO description
 */
7 => [
  'name' => 'playerBuild',
  'description' => clienttranslate('${actplayer} must build'),
  'descriptionmyturn' => clienttranslate('${you} must build'),
  'type' => 'activeplayer',
  'args' => 'argPlayerBuild',
  'action' => 'stCheckEndOfGame',
  'possibleactions' => [ 'build' , 'endgame' ],
  'transitions' => [
    'zombiePass' => 5,
    'built' => 5,
    'endgame' => 99,
  ],
],


/*
 * Final state.
 * Please do not modify.
 */
99 => [
  'name' => 'gameEnd',
  'description' => clienttranslate('End of game'),
  'type' => 'manager',
  'action' => 'stGameEnd',
  'args' => 'argGameEnd'
]

);
