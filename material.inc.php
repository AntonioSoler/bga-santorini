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

require_once("modules/Utils.class.php");
require_once("modules/SantoriniPlayer.class.php");
require_once("modules/SantoriniLog.class.php");
require_once("modules/SantoriniBoard.class.php");
require_once("modules/PlayerManager.class.php");
require_once("modules/PowerManager.class.php");
require_once("modules/SantoriniPower.class.php");
require_once("modules/SantoriniHeroPower.class.php");
foreach (PowerManager::$classes as $className) {
  require_once("modules/powers/$className.class.php");
}

$this->levelNames = [
  0 => clienttranslate('ground level'),
  1 => clienttranslate('level 1'),
  2 => clienttranslate('level 2'),
  3 => clienttranslate('level 3'),
];

$this->pieceNames = [
  'lvl0' => clienttranslate('a block'),
  'lvl1' => clienttranslate('a block'),
  'lvl2' => clienttranslate('a block'),
  'lvl3' => clienttranslate('a dome'),
  'worker' => clienttranslate('a worker'),
  'ram' => clienttranslate('the Ram figure'),
  'tokenTalus' => clienttranslate('the Talus Token'),
];
