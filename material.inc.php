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
 * material.inc.php
 *
 * santorini game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


$classes = [
  'Achilles', 'Adonis', 'Aeolus', 'Aphrodite', 'Apollo', 'Ares', 'Artemis', 'Atalanta', 'Athena', 'Atlas',
  'Bellerophon', 'Bia',
  'Chaos', 'Charon', 'Charybdis', 'Chronus', 'Circe', 'Clio', 'Demeter',
  'Dionysus',
  'Eros', 'Europa',
  'Gaea', 'Graeae',
  'Hades', 'Harpies', 'Hecate', 'Hephaestus', 'Hera', 'Heracles', 'Hermes', 'Hestia', 'Hypnus',
  'Jason',
  'Limus',
  'Medea', 'Medusa', 'Minotaur', 'Moerae', 'Morpheus', 'Nemesis',
  'Odysseus',
  'Pan', 'Persephone', 'Polyphemus', 'Poseidon', 'Prometheus',
  'Selene', 'Siren',
  'Tartarus', 'Terpsichore', 'Theseus', 'Triton',
  'Urania',
  'Zeus'
];

require_once("modules/Power.class.php");
require_once("modules/DummyPower.class.php");

foreach($classes as $className){
  require_once("modules/$className.class.php");

  $this->powers[$className::$id] = [
    'name'    => clienttranslate($className::$name),
    'title'   => clienttranslate($className::$title),
    'golden'  => $className::$golden,
    'hero'    => $className::$hero,
    'power'   => array_map('clienttranslate', $className::$power),
    'banned'  => $className::$banned,
    'players' => $className::$players,
  ];
}
