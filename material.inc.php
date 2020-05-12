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

require_once("modules/SantoriniPlayer.class.php");
require_once("modules/SantoriniLog.class.php");
require_once("modules/SantoriniBoard.class.php");
require_once("modules/PlayerManager.class.php");
require_once("modules/PowerManager.class.php");
require_once("modules/powers/Power.class.php");
require_once("modules/powers/HeroPower.class.php");

foreach (Power::$powersClasses as $id => $className) {
  require_once("modules/powers/$className.class.php");

  $this->powers[$className::getId()] = [
    'id'      => $className::getId(),
    'name'    => $className::getName(),
    'title'   => $className::getTitle(),
    'text'    => $className::getText(),
    'banned'  => $className::getBannedIds(),
    'players' => $className::getPlayers(),
    'golden'  => $className::isGoldenFleece(),
    'hero'    => get_parent_class($className) == 'HeroPower',
  ];
}
