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
    // The initial state. Please do not modify.
    1 => array(
        'name' => 'gameSetup',
        'description' => '',
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => array(
            '' => 2,
        ),
    ),

/*
 * Worker placement
 *  - nextPlayerPlaceWorker : automatically determined the next player who has to place his workers, and if all workers are placed, start the game
 *  - playerPlaceWorker : allow a player to place a worker
 */
    2 => array(
        'name' => 'nextPlayerPlaceWorker',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextPlayerPlaceWorker',
        'transitions' => array(
            'next' => 3,
            'done' => 5,
        ),
        'updateGameProgression' => true,
    ),

    3 => array(
        'name' => 'playerPlaceWorker',
        'description' => clienttranslate('${actplayer} must place a worker'),
        'descriptionmyturn' => clienttranslate('${you} must place a worker'),
        'type' => 'activeplayer',
        'args' => 'argPlaceWorker',
        'possibleactions' => array( 'placeWorker' ),
        'transitions' => array(
            'zombiePass' => 2,
            'workerPlaced' => 2,
        ),
    ),


/*
 * Worker move TODO
 */

    4 => array(
        'name' => 'nextPlayer',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextPlayer',
        'transitions' => array(
            'next' => 5
        ),
        'updateGameProgression' => true,
    ),

    5 => array(
        'name' => 'playerMove',
        'description' => clienttranslate('${actplayer} must move a worker'),
        'descriptionmyturn' => clienttranslate('${you} must move a worker'),
        'type' => 'activeplayer',
        'args' => 'argPlayerMove',
        'action' => 'stCheckEndOfGame',
        'possibleactions' => array( 'moveWorker', 'endgame' ),
        'transitions' => array(
            'zombiePass' => 4,
            'moved' => 6,
            'endgame' => 99,
        ),
    ),


    6 => array(
        'name' => 'playerBuild',
        'description' => clienttranslate('${actplayer} must build'),
        'descriptionmyturn' => clienttranslate('${you} must build'),
        'type' => 'activeplayer',
        'args' => 'argPlayerBuild',
        'action' => 'stCheckEndOfGame',
        'possibleactions' => array( 'build' , 'endgame' ),
        'transitions' => array(
            'zombiePass' => 4,
            'built' => 4,
            'endgame' => 99,
        ),
    ),

    // Final state.
    // Please do not modify.
    99 => array(
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd'
    )
);
