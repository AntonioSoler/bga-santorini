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

require_once("modules/constants.inc.php");

$game_options = [
  OPTION_POWERS => [
    'name' => totranslate('Powers'),
    'values' => [
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
        'description' => totranslate('One powerful ability is available to any player neighboring the Ram figure'),
        'nobeginner' => true,
      ],
      NONE => [
        'name' => totranslate('No Powers'),
        'tmdisplay' => totranslate('No Powers'),
      ],
    ],
    'startcondition' => [
      HEROES => [
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
      ],
      GODS_AND_HEROES => [
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Hero Powers requires exactly 2 players'),
        ],
      ],
      GOLDEN_FLEECE => [
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('Golden Fleece Variant requires exactly 2 players'),
        ],
      ],
      NONE => [
        [
          'type' => 'maxplayers',
          'value' => 2,
          'message' => totranslate('No Powers requires exactly 2 players'),
        ],
      ],
    ],
  ],

  OPTION_SETUP => [
    'name' => totranslate('Setup'),
    'values' => [
      QUICK => [
        'name' => totranslate('Quick Setup'),
        'tmdisplay' => totranslate('Quick Setup'),
      ],
      TOURNAMENT => [
        'name' => totranslate('Tournament Setup'),
        'tmdisplay' => totranslate('Tournament Setup'),
        'description' => totranslate('First player builds an offer from a limited set of power cards randomly selected by BGA'),
      ],
      CUSTOM => [
        'name' => totranslate('Custom Setup'),
        'tmdisplay' => totranslate('Custom Setup'),
        'description' => totranslate('First player builds an offer from all available powers cards'),
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_POWERS,
        'value' => NONE,
      ],
    ],
  ],

  OPTION_TEAMS => [
    'name' => totranslate('Teams'),
    'values' => [
      TEAMS_RANDOM => [
        'name' => totranslate('Random'),
        'description' => totranslate('Only applies with 4 players'),
      ],
      TEAMS_1_AND_2 => [
        'name' => totranslate('By table order (1st/2nd versus 3rd/4th)'),
        'description' => totranslate('Only applies with 4 players'),
      ],
      TEAMS_1_AND_3 => [
        'name' => totranslate('By table order (1st/3rd versus 2nd/4th)'),
        'description' => totranslate('Only applies with 4 players'),
      ],
      TEAMS_1_AND_4 => [
        'name' => totranslate('By table order (1st/4th versus 2nd/3rd)'),
        'description' => totranslate('Only applies with 4 players'),
      ],
    ],
  ],
];



$game_preferences = [
  HELPERS => [
    'name' => totranslate('Display board coordinates/height'),
    'needReload' => false,
    'values' => [
      HELPERS_ENABLED   => ['name' => totranslate('Enabled')],
      HELPERS_DISABLED  => ['name' => totranslate('Disabled')],
    ]
  ],

  CONFIRM => [
    'name' => totranslate('Turn confirmation'),
    'needReload' => false,
    'values' => [
      CONFIRM_TIMER     => ['name' => totranslate('Enabled with timer')],
      CONFIRM_ENABLED   => ['name' => totranslate('Enabled')],
      CONFIRM_DISABLED  => ['name' => totranslate('Disabled')],
    ]
  ],
];
