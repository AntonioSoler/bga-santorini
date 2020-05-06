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
  OPTION_GODS => [
    'name' => totranslate('God Powers'),
    'values' => [
      NO_POWER => [
        'name' => totranslate('Off'),
        'tmdisplay' => totranslate('No God Powers'),
      ],
      SIMPLE_GODS => [
        'name' => totranslate('Simple Gods'),
        'tmdisplay' => totranslate('Simple Gods'),
      ],
      ALL_GODS => [
        'name' => totranslate('All Gods'),
        'tmdisplay' => totranslate('All Gods'),
        'nobeginner' => true,
      ],
      GOLDEN_FLEECE => [
        'name' => totranslate('Golden Fleece Variant'),
        'tmdisplay' => totranslate('Golden Fleece Variant'),
        'nobeginner' => true,
      ],
    ],
    'startcondition' => [
      GOLDEN_FLEECE => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Golden Fleece Variant requires exactly 2 players.'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Golden Fleece Variant requires exactly 2 players.'),
        ],
      ],
    ],
  ],

  OPTION_HEROES => [
    'name' => totranslate('Hero Powers'),
    'values' => [
      OFF => [
        'name' => totranslate('Off')
      ],
      ON => [
        'name' => totranslate('On'),
        'tmdisplay' => totranslate('Hero Powers'),
        'nobeginner' => true,
      ],
    ],
    'startcondition' => [
      ON => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players.'),
        ],
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players.'),
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
      ],
    ],
    'displayconditionoperand' => 'or',
    'displaycondition' => [
      [
        'type' => 'otheroption',
        'id' => OPTION_GODS,
        'value' => [SIMPLE_GODS, ALL_GODS, GOLDEN_FLEECE],
      ],
      [
        'type' => 'otheroption',
        'id' => OPTION_HEROES,
        'value' => [ON],
      ],
    ],
  ],
];
