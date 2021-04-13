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
  OPTION_TEAMS => [
    'name' => totranslate('Teams'),
    'default' => TEAMS_RANDOM,
    'values' => [
      TEAMS_RANDOM => [
        'name' => totranslate('Random'),
      ],
      TEAMS_1_AND_2 => [
        'name' => totranslate('By table order (1st/2nd versus 3rd/4th)'),
      ],
      TEAMS_1_AND_3 => [
        'name' => totranslate('By table order (1st/3rd versus 2nd/4th)'),
      ],
      TEAMS_1_AND_4 => [
        'name' => totranslate('By table order (1st/4th versus 2nd/3rd)'),
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'maxplayers',
        'value' => 4,
      ],
    ],
  ],

  OPTION_GOLDEN_FLEECE => [
    'name' => totranslate('Golden Fleece Variant'),
    'default' => NO,
    'values' => [
      NO => [
        'name' => totranslate('No'),
      ],
      YES => [
        'name' => totranslate('Yes'),
        'tmdisplay' => totranslate('Golden Fleece Variant'),
        'description' => totranslate('One powerful ability is available to any player neighboring the Ram figure'),
        'nobeginner' => true,
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'maxplayers',
        'value' => 2,
      ],
    ],
  ],

  OPTION_SIMPLE => [
    'name' => totranslate('Simple Gods'),
    'default' => YES,
    'values' => [
      NO => [
        'name' => totranslate('No'),
      ],
      YES => [
        'name' => totranslate('Yes'),
        'tmdisplay' => totranslate('Simple Gods'),
        'description' => totranslate('Includes 10 basic powers'),
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_GOLDEN_FLEECE,
        'value' => YES,
      ],
    ],
  ],

  OPTION_HERO => [
    'name' => totranslate('Hero Powers'),
    'default' => NO,
    'values' => [
      NO => [
        'name' => totranslate('No'),
      ],
      YES => [
        'name' => totranslate('Yes'),
        'tmdisplay' => totranslate('Hero Powers'),
        'description' => totranslate('Includes 10 once-per-game powers'),
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_GOLDEN_FLEECE,
        'value' => YES,
      ],
      [
        'type' => 'maxplayers',
        'value' => 2,
      ],
    ],
  ],

  OPTION_ADVANCED => [
    'name' => totranslate('Advanced Gods'),
    'default' => NO,
    'values' => [
      NO => [
        'name' => totranslate('No'),
      ],
      YES => [
        'name' => totranslate('Yes'),
        'tmdisplay' => totranslate('Advanced Gods'),
        'description' => totranslate('Includes all other powers'),
        'nobeginner' => true,
      ],
      PERFECT => [
        'name' => totranslate('Perfect Information'),
        'tmdisplay' => totranslate('Perfect Information'),
        'description' => totranslate('Includes all other powers, except those involving hidden information or luck'),
        'nobeginner' => true,
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_GOLDEN_FLEECE,
        'value' => YES,
      ],
    ],
  ],

  OPTION_SETUP => [
    'name' => totranslate('Setup'),
    'default' => QUICK,
    'values' => [
      QUICK => [
        'name' => totranslate('Quick Setup'),
      ],
      LIMITED => [
        'name' => totranslate('Limited Choice'),
        'description' => totranslate('First player chooses available powers from a limited set'),
      ],
      FULL => [
        'name' => totranslate('Full Choice'),
        'description' => totranslate('First player chooses available powers from the complete set'),
      ],
    ],
    'displayconditionoperand' => 'or',
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_GOLDEN_FLEECE,
        'value' => NO,
      ],
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_SIMPLE,
        'value' => NO,
      ],
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_HERO,
        'value' => NO,
      ],
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_ADVANCED,
        'value' => NO,
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
