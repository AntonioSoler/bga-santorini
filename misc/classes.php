<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * Gods and heroes constants
 */

// Simple gods
define('APOLLO', 1);
define('ARTEMIS', 2);
define('ATHENA', 3);
define('ATLAS', 4);
define('DEMETER', 5);
define('HEPHAESTUS', 6);
define('HERMES', 7);
define('MINOTAUR', 8);
define('PAN', 9);
define('PROMETHEUS', 10);

// Advanced gods
define('APHRODITE', 11);
define('ARES', 12);
define('BIA', 13);
define('CHAOS', 14);
define('CHARON', 15);
define('CHRONUS', 16);
define('CIRCE', 17);
define('DIONYSUS', 18);
define('EROS', 19);
define('HERA', 20);
define('HESTIA', 21);
define('HYPNUS', 22);
define('LIMUS', 23);
define('MEDUSA', 24);
define('MORPHEUS', 25);
define('PERSEPHONE', 26);
define('POSEIDON', 27);
define('SELENE', 28);
define('TRITON', 29);
define('ZEUS', 30);

// Golden Fleece gods
define('AEOLUS', 31);
define('CHARYBDIS', 32);
define('CLIO', 33);
define('EUROPA', 34);
define('GAEA', 35);
define('GRAEAE', 36);
define('HADES', 37);
define('HARPIES', 38);
define('HECATE', 39);
define('MOERAE', 40);
define('NEMESIS', 41);
define('SIREN', 42);
define('TARTARUS', 43);
define('TERPSICHORE', 44);
define('URANIA', 45);

// Hero Power gods
define('ACHILLES', 46);
define('ADONIS', 47);
define('ATALANTA', 48);
define('BELLEROPHON', 49);
define('HERACLES', 50);
define('JASON', 51);
define('MEDEA', 52);
define('ODYSSEUS', 53);
define('POLYPHEMUS', 54);
define('THESEUS', 55);

$r = [];
$r[1] = 'APOLLO';
$r[2] = 'ARTEMIS';
$r[3] = 'ATHENA';
$r[4] = 'ATLAS';
$r[5] = 'DEMETER';
$r[6] = 'HEPHAESTUS';
$r[7] = 'HERMES';
$r[8] = 'MINOTAUR';
$r[9] = 'PAN';
$r[10] = 'PROMETHEUS';
$r[11] = 'APHRODITE';
$r[12] = 'ARES';
$r[13] = 'BIA';
$r[14] = 'CHAOS';
$r[15] = 'CHARON';
$r[16] = 'CHRONUS';
$r[17] = 'CIRCE';
$r[18] = 'DIONYSUS';
$r[19] = 'EROS';
$r[20] = 'HERA';
$r[21] = 'HESTIA';
$r[22] = 'HYPNUS';
$r[23] = 'LIMUS';
$r[24] = 'MEDUSA';
$r[25] = 'MORPHEUS';
$r[26] = 'PERSEPHONE';
$r[27] = 'POSEIDON';
$r[28] = 'SELENE';
$r[29] = 'TRITON';
$r[30] = 'ZEUS';
$r[31] = 'AEOLUS';
$r[32] = 'CHARYBDIS';
$r[33] = 'CLIO';
$r[34] = 'EUROPA';
$r[35] = 'GAEA';
$r[36] = 'GRAEAE';
$r[37] = 'HADES';
$r[38] = 'HARPIES';
$r[39] = 'HECATE';
$r[40] = 'MOERAE';
$r[41] = 'NEMESIS';
$r[42] = 'SIREN';
$r[43] = 'TARTARUS';
$r[44] = 'TERPSICHORE';
$r[45] = 'URANIA';
$r[46] = 'ACHILLES';
$r[47] = 'ADONIS';
$r[48] = 'ATALANTA';
$r[49] = 'BELLEROPHON';
$r[50] = 'HERACLES';
$r[51] = 'JASON';
$r[52] = 'MEDEA';
$r[53] = 'ODYSSEUS';
$r[54] = 'POLYPHEMUS';
$r[55] = 'THESEUS';


function clienttranslate($str){
  return $str;
}

$gods = array(
    // Simple Gods
    APOLLO => array(
        'name'   => clienttranslate('Apollo'),
        'title'  => clienttranslate('God Of Music'),
        'power'  => array(
            clienttranslate('Your Move: Your Worker may move into an opponent Worker\'s space by forcing their Worker to the space yours just vacated.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    ARTEMIS => array(
        'name'   => clienttranslate('Artemis'),
        'title'  => clienttranslate('Goddess of the Hunt'),
        'power'  => array(
            clienttranslate('Your Move: Your Worker may move one additional time, but not back to its initial space.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    ATHENA => array(
        'name'   => clienttranslate('Athena'),
        'title'  => clienttranslate('Goddess of Wisdom'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: If one of your Workers moved up on your last turn, opponent Workers cannot move up this turn.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    ATLAS => array(
        'name'   => clienttranslate('Atlas'),
        'title'  => clienttranslate('Titan Shouldering the Heavens'),
        'power'  => array(
            clienttranslate('Your Build: Your Worker may build a dome at any level.'),
        ),
        'banned'  => array(GAEA),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    DEMETER => array(
        'name'   => clienttranslate('Demeter'),
        'title'  => clienttranslate('Goddess of the Harvest'),
        'power'  => array(
            clienttranslate('Your Build: Your Worker may build one additional time, but not on the same space.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HEPHAESTUS => array(
        'name'   => clienttranslate('Hephaestus'),
        'title'  => clienttranslate('God of Blacksmiths'),
        'power'  => array(
            clienttranslate('Your Build: Your Worker may build one additional block (not dome) on top of your first block.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HERMES => array(
        'name'   => clienttranslate('Hermes'),
        'title'  => clienttranslate('God of Travel'),
        'power'  => array(
            clienttranslate('Your Turn: If your Workers do not move up or down, they may each move any number of times (even zero), and then either builds.'),
        ),
        'banned'  => array(HARPIES),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    MINOTAUR => array(
        'name'   => clienttranslate('Minotaur'),
        'title'  => clienttranslate('Bull-headed Monster'),
        'power'  => array(
            clienttranslate('Your Move: Your Worker may move into an opponent Worker\'s space, if their Worker can be forced one space straight backwards to an unoccupied space at any level.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    PAN => array(
        'name'   => clienttranslate('Pan'),
        'title'  => clienttranslate('God of the Wild'),
        'power'  => array(
            clienttranslate('Win Condition: You also win if your Worker moves down two or more levels.'),
        ),
        'banned'  => array(HADES),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    PROMETHEUS => array(
        'name'   => clienttranslate('Prometheus'),
        'title'  => clienttranslate('Titan Benefactor of Mankind'),
        'power'  => array(
            clienttranslate('Your Turn: If your Worker does not move up, it may build both before and after moving.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    // Advanced Gods
    APHRODITE => array(
        'name'   => clienttranslate('Aphrodite'),
        'title'  => clienttranslate('Goddess of Love'),
        'power'  => array(
            clienttranslate('Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers.'),
        ),
        'banned'  => array(NEMESIS, URANIA),
        'players' => array(2, 4),
        'golden'  => false,
    ),

    ARES => array(
        'name'   => clienttranslate('Ares'),
        'title'  => clienttranslate('God of War'),
        'power'  => array(
            clienttranslate('End of Your Turn: You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    BIA => array(
        'name'   => clienttranslate('Bia'),
        'title'  => clienttranslate('Goddess of Violence'),
        'power'  => array(
            clienttranslate('Setup: Place your Workers first.'),
            clienttranslate('Your Move: If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent\'s Worker is removed from the game.')
        ),
        'banned'  => array(NEMESIS, TARTARUS),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    CHAOS => array(
        'name'   => clienttranslate('Chaos'),
        'title'  => clienttranslate('Primordial Nothingness'),
        'power'  => array(
            clienttranslate('Setup: Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck.'),
            clienttranslate('Any Time: You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one.')
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    CHARON => array(
        'name'   => clienttranslate('Charon'),
        'title'  => clienttranslate('Ferryman to the Underworld'),
        'power'  => array(
            clienttranslate('Your Move: Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied.'),
        ),
        'banned'  => array(HECATE),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    CHRONUS => array(
        'name'   => clienttranslate('Chronus'),
        'title'  => clienttranslate('God of Time'),
        'power'  => array(
            clienttranslate('Win Condition: You also win when there are at least five Complete Towers on the board.'),
        ),
        'banned'  => array(),
        'players' => array(2),
        'golden'  => false,
    ),

    CIRCE => array(
        'name'   => clienttranslate('Circe'),
        'title'  => clienttranslate('Divine Enchantress'),
        'power'  => array(
            clienttranslate('Start of Your Turn: If an opponent\'s Workers do not neighbor each other, you alone have use of their power until your next turn.'),
        ),
        'banned'  => array(CLIO, HECATE),
        'players' => array(2),
        'golden'  => false,
    ),

    DIONYSUS => array(
        'name'   => clienttranslate('Dionysus'),
        'title'  => clienttranslate('God of Wine'),
        'power'  => array(
            clienttranslate('Your Build: Each time a Worker you control creates a Complete Tower, you may take an additional turn using an opponent Worker instead of your own. No player can win during these additional turns.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    EROS => array(
        'name'   => clienttranslate('Eros'),
        'title'  => clienttranslate('God of Desire'),
        'power'  => array(
            clienttranslate('Setup: Place your Workers anywhere along opposite edges of the board.'),
            clienttranslate('Win Condition: You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game).')
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    HERA => array(
        'name'   => clienttranslate('Hera'),
        'title'  => clienttranslate('Goddess of Marriage'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: An opponent cannot win by moving into a perimeter space.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HESTIA => array(
        'name'   => clienttranslate('Hestia'),
        'title'  => clienttranslate('Goddess of Hearth and Home'),
        'power'  => array(
            clienttranslate('Your Build: Your Worker may build one additional time, but this cannot be on a perimeter space.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HYPNUS => array(
        'name'   => clienttranslate('Hypnus'),
        'title'  => clienttranslate('God of Sleep'),
        'power'  => array(
            clienttranslate('Start of Opponent\'s Turn: If one of your opponent\'s Workers is higher than all of their others, it cannot move.'),
        ),
        'banned'  => array(TERPSICHORE),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    LIMUS => array(
        'name'   => clienttranslate('Limus'),
        'title'  => clienttranslate('Goddess of Famine'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: Opponent Workers cannot build on spaces neighboring your Workers, unless building a dome to create a Complete Tower.'),
        ),
        'banned'  => array(TERPSICHORE),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    MEDUSA => array(
        'name'   => clienttranslate('Medusa'),
        'title'  => clienttranslate('Petrifying Gorgon'),
        'power'  => array(
            clienttranslate('End of Your Turn: If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game.'),
        ),
        'banned'  => array(NEMESIS),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    MORPHEUS => array(
        'name'   => clienttranslate('Morpheus'),
        'title'  => clienttranslate('God of Dreams'),
        'power'  => array(
            clienttranslate('Start of Your Turn: Place a block or dome on your God Power card.'),
            clienttranslate('Your Build: Your Worker cannot build as normal. Instead, your Worker may build any number of times (even zero) using blocks / domes collected on your God Power card. At any time, any player may exchange a block / dome on the God Power card for dome or a block of a different shape.')
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    PERSEPHONE => array(
        'name'   => clienttranslate('Persephone'),
        'title'  => clienttranslate('Goddess of Spring Growth'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: If possible, at least one Worker must move up this turn.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    POSEIDON => array(
        'name'   => clienttranslate('Poseidon'),
        'title'  => clienttranslate('God of the Sea'),
        'power'  => array(
            clienttranslate('End of Your Turn: If your unmoved Worker is on the ground level, it may build up to three times.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    SELENE => array(
        'name'   => clienttranslate('Selene'),
        'title'  => clienttranslate('Goddess of the Moon'),
        'power'  => array(
            clienttranslate('Your Build: Instead of your normal build, your female Worker may build a dome at any level regardless of which Worker moved. '),
        ),
        'banned'  => array(GAEA),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    TRITON => array(
        'name'   => clienttranslate('Triton'),
        'title'  => clienttranslate('God of the Waves'),
        'power'  => array(
            clienttranslate('Your Move: Each time your Worker moves into a perimeter space, it may immediately move again.'),
        ),
        'banned'  => array(HARPIES),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    ZEUS => array(
        'name'   => clienttranslate('Zeus'),
        'title'  => clienttranslate('God of the Sky'),
        'power'  => array(
            clienttranslate('Your Build: Your Worker may build a block under itself.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    // Golden Fleece Gods
    AEOLUS => array(
        'name'   => clienttranslate('Aeolus'),
        'title'  => clienttranslate('God of the Winds'),
        'power'  => array(
            clienttranslate('Setup: Place the Wind Token beside the board and orient it in any of the 8 directions to indicate which direction the Wind is blowing.'),
            clienttranslate('End of Your Turn: Orient the Wind Token to any of the the eight directions.'),
            clienttranslate('Any Move: Workers cannot move directly into the Wind.')
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    CHARYBDIS => array(
        'name'   => clienttranslate('Charybdis'),
        'title'  => clienttranslate('Whirlpool Monster'),
        'power'  => array(
            clienttranslate('Setup: Place 2 Whirlpool Tokens on your God Power card.'),
            clienttranslate('End of Your Turn: You may place a Whirlpool Token from your God Power card on any unoccupied space on the board.'),
            clienttranslate('Any Time: When both Whirlpool Tokens are in unoccupied spaces, a Worker that moves onto a space containing a Whirlpool Token must immediately move to the other Whirlpool Token\'s space. This move is considered to be in the same direction as the previous move. When a Whirlpool Token is built on or removed from the board, it is returned to your God Power card.')
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    CLIO => array(
        'name'   => clienttranslate('Clio'),
        'title'  => clienttranslate('Muse of History'),
        'power'  => array(
            clienttranslate('Your Build: Place a Coin Token on each of the first 3 blocks your Workers build.'),
            clienttranslate('Opponent\'s Turn: Opponents treat spaces containing your Coin Tokens as if they contain only a dome.')
        ),
        'banned'  => array(CIRCE, NEMESIS),
        'players' => array(2, 3),
        'golden'  => false,
    ),

    EUROPA => array(
        'name'   => clienttranslate('Europa & Talus'),
        'title'  => clienttranslate('Queen & Guardian Automaton'),
        'power'  => array(
            clienttranslate('Setup: Place the Talus Token on your God Power card.'),
            clienttranslate('End of Your Turn: You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved.'),
            clienttranslate('Any Time: All players treat the space containing the Talus Token as if it contains only a dome.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    GAEA => array(
        'name'   => clienttranslate('Gaea'),
        'title'  => clienttranslate('Goddess of the Earth'),
        'power'  => array(
            clienttranslate('Setup: Take 2 extra Workers of your color. These are kept on your God Power card until needed.'),
            clienttranslate('Any Build: When a Worker builds a dome, Gaea may immediately place a Worker from her God Power card onto a ground-level space neighboring the dome.'),
        ),
        'banned'  => array(ATLAS, NEMESIS, SELENE),
        'players' => array(2, 3),
        'golden'  => false,
    ),

    GRAEAE => array(
        'name'   => clienttranslate('Graeae'),
        'title'  => clienttranslate('The Gray Hags'),
        'power'  => array(
            clienttranslate('Setup: When placing your Workers, place 3 of your color.'),
            clienttranslate('Your Build: You choose which Worker of yours builds.'),
        ),
        'banned'  => array(NEMESIS),
        'players' => array(2, 3),
        'golden'  => false,
    ),

    HADES => array(
        'name'   => clienttranslate('Hades'),
        'title'  => clienttranslate('God of the Underworld'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: Opponent Workers cannot move down.'),
        ),
        'banned'  => array(PAN),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HARPIES => array(
        'name'   => clienttranslate('Harpies'),
        'title'  => clienttranslate('Winged Menaces'),
        'power'  => array(
            clienttranslate('Opponent\'s Turn: Each time an opponent\'s Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed.'),
        ),
        'banned'  => array(HERMES, TRITON),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    HECATE => array(
        'name'   => clienttranslate('Hecate'),
        'title'  => clienttranslate('Goddess of Magic'),
        'power'  => array(
            clienttranslate('Setup: Take the Map, Shield, and 2 Worker Tokens. Hide the Map behind the Shield and secretly place your Worker Tokens on the Map to represent the location of your Workers on the game board. Place your Workers last.'),
            clienttranslate('Your Turn: Move a Worker Token on the Map as if it were on the game board. Build on the game board, as normal.'),
            clienttranslate('Any Time: If an opponent attempts an action that would not be legal due to the presence of your secret Workers, their action is cancelled and they lose the rest of their turn. When possible, use their power on their behalf to make their turns legal without informing them.'),
        ),
        'banned'  => array(CHARON, CIRCE, MOERAE, TARTARUS),
        'players' => array(2, 3),
        'golden'  => false,
    ),

    MOERAE => array(
        'name'   => clienttranslate('Moerae'),
        'title'  => clienttranslate('Goddesses of Fate'),
        'power'  => array(
            clienttranslate('Setup: Take the Map, Shield, and Fate Token. Behind your Shield, secretly select a 2 X 2 square of Fate spaces by placing your Fate Token on the Map. When placing your Workers, place 3 of your color. '),
            clienttranslate('Win Condition: If an opponent Worker attempts to win by moving into one of your Fate spaces, you win instead.'),
        ),
        'banned'  => array(HECATE, NEMESIS, TARTARUS),
        'players' => array(2, 3),
        'golden'  => false,
    ),

    NEMESIS => array(
        'name'   => clienttranslate('Nemesis'),
        'title'  => clienttranslate('Goddess of Retribution'),
        'power'  => array(
            clienttranslate('End of Your Turn: If none of an opponent\'s Workers neighbor yours, you may force as many of your opponent\'s Workers as possible to take the spaces you occupy, and vice versa.'),
        ),
        'banned'  => array(CLIO, GAEA, GRAEAE, MOERAE, APHRODITE, BIA, MEDUSA, TERPSICHORE, THESEUS),
        'players' => array(2, 3, 4),
        'golden'  => false,
    ),

    SIREN => array(
        'name'   => clienttranslate('Siren'),
        'title'  => clienttranslate('Alluring Sea Nymph'),
        'power'  => array(
            clienttranslate('Setup: Place the Arrow Token beside the board and orient it in any of the 8 directions to indicate the direction of the Siren\'s Song.'),
            clienttranslate('Your Turn: You may choose not to take your normal turn. Instead, force one or more opponent Workers one space in the direction of the Siren\'s Song to unoccupied spaces at any level.'),
        ),
        'banned'  => array(),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    TARTARUS => array(
        'name'   => clienttranslate('Tartarus'),
        'title'  => clienttranslate('God of the Abyss'),
        'power'  => array(
            clienttranslate('Setup: Take the Map, Shield, and one Abyss Token. Place your Workers first. After all players\' Workers are placed, hide the Map behind the Shield and secretly place your Abyss Token on an unoccupied space. This space is the Abyss.'),
            clienttranslate('Lose Condition: If any player\'s Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss.'),
        ),
        'banned'  => array(BIA, HECATE, MOERAE, TERPSICHORE),
        'players' => array(2),
        'golden'  => false,
    ),

    TERPSICHORE => array(
        'name'   => clienttranslate('Terpsichore'),
        'title'  => clienttranslate('Muse of Dancing'),
        'power'  => array(
            clienttranslate('Your Turn: All of your Workers must move, and then all must build.'),
        ),
        'banned'  => array(NEMESIS, HYPNUS, LIMUS, TARTARUS),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),

    URANIA => array(
        'name'   => clienttranslate('Urania'),
        'title'  => clienttranslate('Muse of Astronomy'),
        'power'  => array(
            clienttranslate('Your Turn: When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors.'),
        ),
        'banned'  => array(APHRODITE),
        'players' => array(2, 3, 4),
        'golden'  => true,
    ),
);


// Hero Powers
$heroes = array(
    ACHILLES => array(
        'name'   => clienttranslate('Achilles'),
        'title'  => clienttranslate('Volatile Warrior'),
        'power'  => array(
            clienttranslate('Your Turn: Once, your Worker builds both before and after moving.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    ADONIS => array(
        'name'   => clienttranslate('Adonis'),
        'title'  => clienttranslate('Devastatingly Handsome'),
        'power'  => array(
            clienttranslate('End of Your Turn: Once, choose an opponent Worker. If possible, that Worker must be neighboring one of your Workers at the end of their next turn.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    ATALANTA => array(
        'name'   => clienttranslate('Atalanta'),
        'title'  => clienttranslate('Swift Huntress'),
        'power'  => array(
            clienttranslate('Your Move: Once, your Worker moves any number of additional times.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    BELLEROPHON => array(
        'name'   => clienttranslate('Bellerophon'),
        'title'  => clienttranslate('Tamer of Pegasus'),
        'power'  => array(
            clienttranslate('Your Move: Once, your Worker moves up two levels.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    HERACLES => array(
        'name'   => clienttranslate('Heracles'),
        'title'  => clienttranslate('Doer of Great Deeds'),
        'power'  => array(
            clienttranslate('End of Your Turn: Once, both your Workers build any number of domes (even zero) at any level.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    JASON => array(
        'name'   => clienttranslate('Jason'),
        'title'  => clienttranslate('Leader of the Argonauts'),
        'power'  => array(
            clienttranslate('Setup: Take one extra Worker of your color. This is kept on your God Power card until needed.'),
            clienttranslate('Your Turn: Once, instead of your normal turn, place your extra Worker on an unoccupied ground-level perimeter space. This Worker then builds.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    MEDEA => array(
        'name'   => clienttranslate('Medea'),
        'title'  => clienttranslate('Powerful Sorceress'),
        'power'  => array(
            clienttranslate('End of Your Turn: Once, remove one block from under any number of Workers neighboring your unmoved Worker. You also remove any Tokens on the blocks.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    ODYSSEUS => array(
        'name'   => clienttranslate('Odysseus'),
        'title'  => clienttranslate('Cunning Leader'),
        'power'  => array(
            clienttranslate('Start of Your Turn: Once, force to unoccupied corner spaces any number of opponent Workers that neighbor your Workers.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    POLYPHEMUS => array(
        'name'   => clienttranslate('Polyphemus'),
        'title'  => clienttranslate('Gigantic Cyclops'),
        'power'  => array(
            clienttranslate('End of Your Turn: Once, your Worker builds up to 2 domes at any level on any unoccupied spaces on the board.'),
        ),
        'banned'  => array(),
        'players' => array(2),
    ),

    THESEUS => array(
        'name'   => clienttranslate('Theseus'),
        'title'  => clienttranslate('Slayer of the Minotaur'),
        'power'  => array(
            clienttranslate('End of Your Turn: Once, if any of your Workers is exactly 2 levels below any neighboring opponent Workers, remove one of those opponent Workers from play.'),
        ),
        'banned'  => array(NEMESIS),
        'players' => array(2),
    )
);
/*
echo "\$rpowers = [<br />";
foreach($gods as $id => $god){
  echo "  ".$r[$id]." => '".ucfirst(strtolower($r[$id]))."', <br />";
}
foreach($heroes as $id => $god){
  echo "  ".$r[$id]." => '".ucfirst(strtolower($r[$id]))."', <br />";
}
*/

/*
foreach($gods as $id => $god){
    $fp = fopen("Classes/".$god['name'].'.class.php', 'w');
    fwrite($fp, "<?php

class ".$god['name']." extends Power
{
  public static function getId() {
    return ".$r[$id].";
  }

  public static function getName() {
    return clienttranslate('".$god['name']."');
  }

  public static function getTitle() {
    return clienttranslate('".$god['title']."');
  }

  public static function getText() {
    return [
".
        implode(",\n", array_map(function($a){ return '      clienttranslate("'.$a.'")'; }, $god['power']))
."
    ];
  }

  public static function getPlayers() {
    return [".implode(', ', $god['players'])."];
  }

  public static function getBannedIds() {
    return [".implode(', ', array_map(function($a) use($r){ return $r[$a]; }, $god['banned']) ) ."];
  }

  public static function isGoldenFleece() {
    return ".($god['golden']? "true":"false")."; 
  }

  / * * * /

}
  ");
  fclose($fp);
}
*/

foreach($heroes as $id => $god){
    $fp = fopen("Classes/".$god['name'].'.class.php', 'w');
    fwrite($fp, "<?php

class ".$god['name']." extends HeroPower
{
  public static function getId() {
    return ".$r[$id].";
  }

  public static function getName() {
    return clienttranslate('".$god['name']."');
  }

  public static function getTitle() {
    return clienttranslate('".$god['title']."');
  }

  public static function getText() {
    return [
".
        implode(",\n", array_map(function($a){ return '      clienttranslate("'.$a.'")'; }, $god['power']))
."
    ];
  }

  public static function getBannedIds() {
    return [".implode(', ', array_map(function($a) use($r){ return $r[$a]; }, $god['banned']) ) ."];
  }

  /* * */

}
  ");
  fclose($fp);
}