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
 * stats.inc.php
 *
 * santorini game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.

    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice ("Your game configuration" section):
    http://en.studio.boardgamearena.com/admin/studio

    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean

    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.

    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress

    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players

*/

require_once('modules/constants.inc.php');
require_once("modules/PowerManager.class.php");
require_once("modules/SantoriniPower.class.php");
require_once("modules/SantoriniHeroPower.class.php");
foreach (PowerManager::$classes as $className) {
    require_once("modules/powers/$className.class.php");
}
$powerManager = new PowerManager(null);
$powerLabels = $powerManager->getStatLabels();

$stats_type = [
    // Statistics global to table
    'table' => [
        'winPower' => [
            'id' => STAT_POWER,
            'name' => totranslate('Winning Power (2 or 3 players)'),
            'type' => 'int'
        ],
        'winPower1' => [
            'id' => STAT_POWER1,
            'name' => totranslate('Winning Power (4 players)'),
            'type' => 'int'
        ],
        'winPower2' => [
            'id' => STAT_POWER2,
            'name' => totranslate('Winning Power (4 players)'),
            'type' => 'int'
        ],
        'move' => [
            'id' => STAT_MOVE,
            'name' => totranslate('Moves'),
            'type' => 'int'
        ],
        'buildBlock' => [
            'id' => STAT_BUILD_BLOCK,
            'name' => totranslate('Blocks built'),
            'type' => 'int'
        ],
        'buildDome' => [
            'id' => STAT_BUILD_DOME,
            'name' => totranslate('Domes built'),
            'type' => 'int'
        ],
        'buildTower' => [
            'id' => STAT_BUILD_TOWER,
            'name' => totranslate('Complete Towers built'),
            'type' => 'int'
        ],
    ],

    // Statistics existing for each player
    'player' => [
        'playerPower' => [
            'id' => STAT_POWER,
            'name' => totranslate('Power'),
            'type' => 'int'
        ],
        'usePower' => [
            'id' => STAT_USE_POWER,
            'name' => totranslate('Power uses'),
            'type' => 'int'
        ],
        'move' => [
            'id' => STAT_MOVE,
            'name' => totranslate('Moves'),
            'type' => 'int'
        ],
        'moveUp' => [
            'id' => STAT_MOVE_UP,
            'name' => totranslate('Moves up'),
            'type' => 'int'
        ],
        'moveDown' => [
            'id' => STAT_MOVE_DOWN,
            'name' => totranslate('Moves down'),
            'type' => 'int'
        ],
        'buildBlock' => [
            'id' => STAT_BUILD_BLOCK,
            'name' => totranslate('Blocks built'),
            'type' => 'int'
        ],
        'buildDome' => [
            'id' => STAT_BUILD_DOME,
            'name' => totranslate('Domes built'),
            'type' => 'int'
        ],
    ],

    'value_labels' => [
        STAT_POWER => $powerLabels,
        STAT_POWER1 => $powerLabels,
        STAT_POWER2 => $powerLabels,
    ]
];
