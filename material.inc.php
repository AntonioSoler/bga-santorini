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
