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

$this->rotations = array( 0, 60, 120, 180, 240, 300 );

$this->terrain = array(
    VOLCANO => clienttranslate('volcano'),
    JUNGLE => clienttranslate('jungle'),
    GRASS => clienttranslate('clearing'),
    SAND => clienttranslate('sand'),
    ROCK => clienttranslate('rock'),
    LAKE => clienttranslate('lake'),
);

$this->buildings = array(
    HUT => clienttranslate('huts'),
    TEMPLE => clienttranslate('temples'),
    TOWER => clienttranslate('towers'),
);
