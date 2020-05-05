<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * santorini implementation : © quietmint
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * santorini game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in santorini.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(
    100 => array(
        'name' => totranslate('God Powers'),
        'values' => array(
            0 => array(
                'name' => totranslate('Off'),
                'tmdisplay' => totranslate('No God Powers'),
            ),
            1 => array(
                'name' => totranslate('Simple Gods'),
                'tmdisplay' => totranslate('Simple Gods'),
            ),
            2 => array(
                'name' => totranslate('All Gods'),
                'tmdisplay' => totranslate('All Gods'),
                'nobeginner' => true,
            ),
            3 => array(
                'name' => totranslate('Golden Fleece Variant'),
                'tmdisplay' => totranslate('Golden Fleece Variant'),
                'nobeginner' => true,
            ),
        ),
        'startcondition' => array(
            3 => array(
                array(
                    'type' => 'minplayers',
                    'value' => 2,
                    'message' => totranslate('Golden Fleece Variant requires exactly 2 players.'),
                ),
                array(
                    'type' => 'maxplayers',
                    'value' => 2,
                    'message' => totranslate('Golden Fleece Variant requires exactly 2 players.'),
                ),
            ),
        ),
    ),

    101 => array(
        'name' => totranslate('Hero Powers'),
        'values' => array(
            0 => array(
                'name' => totranslate('Off')
            ),
            1 => array(
                'name' => totranslate('On'),
                'tmdisplay' => totranslate('Hero Powers'),
                'nobeginner' => true,
            ),
        ),
        'startcondition' => array(
            1 => array(
                array(
                    'type' => 'minplayers',
                    'value' => 2,
                    'message' => totranslate('Hero Powers requires exactly 2 players.'),
                ),
                array(
                    'type' => 'maxplayers',
                    'value' => 2,
                    'message' => totranslate('Hero Powers requires exactly 2 players.'),
                ),
            ),
        ),
    ),

    102 => array(
        'name' => totranslate('Assignment of Powers'),
        'values' => array(
            0 => array(
                'name' => totranslate('Random'),
            ),
            1 => array(
                'name' => totranslate('Divide and Choose'),
                'tmdisplay' => totranslate('Divide and Choose'),
            ),
        ),
        'displayconditionoperand' => 'or',
        'displaycondition' => array(
            array(
                'type' => 'otheroption',
                'id' => 100,
                'value' => array(1, 2, 3),
            ),
            array(
                'type' => 'otheroption',
                'id' => 102,
                'value' => array(1),
            ),
        ),
    ),
);
