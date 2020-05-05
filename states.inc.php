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
ST_GAME_SETUP => [
  'name' => 'gameSetup',
  'description' => '',
  'type' => 'manager',
  'action' => 'stGameSetup',
  'transitions' => [
    '' => ST_GODS_SETUP,
  ],
],

/*
 * The God Powers setup state.
 * TODO: game state for the moment, but depending on game mode, might be different
 */
ST_GODS_SETUP => [
  'name' => 'godsSetup',
  'description' => '',
  'type' => 'game',
  'action' => 'stGodsSetup',
  'transitions' => [
    'done' => ST_HEROES_SETUP,
  ],
],

/*
 * The Hero Powers setup state.
 * TODO: game state for the moment, but depending on game mode, might be different
 */
ST_HEROES_SETUP => [
  'name' => 'heroesSetup',
  'description' => '',
  'type' => 'game',
  'action' => 'stHeroesSetup',
  'transitions' => [
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


/*
 * TODO description
 */
ST_NEXT_PLAYER => [
  'name' => 'nextPlayer',
  'description' => '',
  'type' => 'game',
  'action' => 'stNextPlayer',
  'transitions' => [
    'next' => ST_MOVE
  ],
  'updateGameProgression' => true,
],


/*
 * Worker move TODO description
 */
ST_MOVE => [
  'name' => 'playerMove',
  'description' => clienttranslate('${actplayer} must move a worker'),
  'descriptionmyturn' => clienttranslate('${you} must move a worker'),
  'type' => 'activeplayer',
  'args' => 'argPlayerMove',
  'action' => 'stCheckEndOfGame',
  'possibleactions' => [ 'moveWorker', 'endgame' ],
  'transitions' => [
    'zombiePass' => ST_NEXT_PLAYER,
    'moved' => ST_BUILD,
    'endgame' => ST_GAME_END,
  ],
],

/*
 * Build TODO description
 */
ST_BUILD => [
  'name' => 'playerBuild',
  'description' => clienttranslate('${actplayer} must build'),
  'descriptionmyturn' => clienttranslate('${you} must build'),
  'type' => 'activeplayer',
  'args' => 'argPlayerBuild',
  'action' => 'stCheckEndOfGame',
  'possibleactions' => [ 'build' , 'endgame' ],
  'transitions' => [
    'zombiePass' => ST_NEXT_PLAYER,
    'built' => ST_NEXT_PLAYER,
    'endgame' => ST_GAME_END,
  ],
],


/*
 * Final state.
 * Please do not modify.
 */
ST_GAME_END => [
  'name' => 'gameEnd',
  'description' => clienttranslate('End of game'),
  'type' => 'manager',
  'action' => 'stGameEnd',
  'args' => 'argGameEnd'
]

);
