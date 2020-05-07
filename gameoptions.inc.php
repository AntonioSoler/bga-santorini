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

require_once("constants.inc.php");

$game_options = [
  OPTION_POWERS => [
    'name' => totranslate('Powers'),
    'values' => [
      NONE => [
        'name' => totranslate('No Powers'),
        'tmdisplay' => totranslate('No Powers'),
      ],
      SIMPLE => [
        'name' => totranslate('Simple Gods'),
        'tmdisplay' => totranslate('Simple Gods'),
        'description' => totranslate('Each player receives a powerful ongoing ability')
      ],
      GODS => [
        'name' => totranslate('All Gods'),
        'tmdisplay' => totranslate('All Gods'),
        'description' => totranslate('Each player receives a powerful ongoing ability'),
        'nobeginner' => true,
      ],
      HEROES => [
        'name' => totranslate('Hero Powers'),
        'tmdisplay' => totranslate('Hero Powers'),
        'description' => totranslate('Each player receives a once-per-game ability'),
        'nobeginner' => true,
      ],
      GODS_AND_HEROES => [
        'name' => totranslate('All Gods and Hero Powers'),
        'tmdisplay' => totranslate('All Gods and Hero Powers'),
        'description' => totranslate('Allows for balanced games between 2 players of unequal skill, with the more experienced choosing a Hero Power and the less experienced choosing a God Power'),
      ],
      GOLDEN_FLEECE => [
        'name' => totranslate('Golden Fleece Variant'),
        'tmdisplay' => totranslate('Golden Fleece Variant'),
        'description' => totranslate('One powerful ability is available to any player touching the Ram figure'),
        'nobeginner' => true,
      ],
    ],
    'startcondition' => [
      GOLDEN_FLEECE => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Golden Fleece Variant requires exactly 2 players'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Golden Fleece Variant requires exactly 2 players'),
        ],
      ],
      HEROES => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
      ],
      GODS_AND_HEROES => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
      ],
    ],
  ],

  OPTION_SETUP => [
    'name' => totranslate('Assignment of Powers'),
    'values' => [
      RANDOM => [
        'name' => totranslate('Random'),
      ],
      FAIR_DIVISION => [
        'name' => totranslate('Fair Division'),
        'tmdisplay' => totranslate('Fair Division'),
        'description' => totranslate('First player chooses all possible Powers, last player chooses first among these'),
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => OPTION_POWERS,
        'value' => [SIMPLE, GODS, HEROES, GOLDEN_FLEECE],
      ],
    ],
  ],
];
