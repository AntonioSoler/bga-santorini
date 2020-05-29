<?php
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

// Promo cards gods
define('TYCHE', 56);
define('SCYLLA', 57);
define('CASTOR', 58);
define('PROTEUS', 59);
define('ERIS', 60);
define('MAENADS', 61);
define('ASTERIA', 62);
define('HIPPOLYTA', 63);
define('HYDRA', 64);
define('IRIS', 65);
define('NYX', 66);
define('PEGASUS', 67);


/*
 * State constants
 */
define('ST_GAME_SETUP', 1);
define('ST_POWERS_SETUP', 10);
define('ST_BUILD_OFFER', 12);
define('ST_POWERS_NEXT_PLAYER_CHOOSE', 13);
define('ST_POWERS_CHOOSE', 14);
define('ST_CHOOSE_FIRST_PLAYER', 18);

define('ST_NEXT_PLAYER_PLACE_WORKER', 3);
define('ST_PLACE_WORKER', 4);

define('ST_NEXT_PLAYER', 5);
define('ST_START_OF_TURN', 16);
define('ST_MOVE', 6);
define('ST_BUILD', 7);
define('ST_END_OF_TURN', 17);
define('ST_USE_POWER', 15);

define('ST_GAME_END', 99);

/*
 * Options constants
 */
define('OPTION_POWERS', 100);
define('SIMPLE', 1);
define('GODS', 2);
define('HEROES', 3);
define('GODS_AND_HEROES', 4);
define('GOLDEN_FLEECE', 5);
define('NONE', 6);

define('OPTION_SETUP', 102);
define('RANDOM', 0);
define('FAIR_DIVISION', 1);

/*
 * Variable constants
 */
define('ROTATE_N', 1);
define('ROTATE_NE', 2);
define('ROTATE_E', 3);
define('ROTATE_SE', 4);
define('ROTATE_S', 5);
define('ROTATE_SW', 6);
define('ROTATE_W', 7);
define('ROTATE_NN', 8);

/*
 * Global game variables
 */
define('CURRENT_ROUND', 20);
define('FIRST_PLAYER', 21);
