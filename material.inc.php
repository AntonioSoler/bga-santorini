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
require_once("modules/Presets.class.php");
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

$this->msg = [
  'offer2' => clienttranslate('${player_name} offers powers ${power_name1} and ${power_name2} for selection'),
  'offer3' => clienttranslate('${player_name} offers powers ${power_name1}, ${power_name2}, and ${power_name3} for selection'),
  'offer4' => clienttranslate('${player_name} offers powers ${power_name1}, ${power_name2}, ${power_name3}, and ${power_name4} for selection'),
  'firstPlayer' => clienttranslate('${power_name} will start this game'),
  'specialPower' => clienttranslate('${player_name} chooses ${power_name} as ${special_name}'),
  'colorPlayer' => clienttranslate('${player_name} controls of the ${color} workers'),
  'colorTeam' => clienttranslate('${player_name} and ${player_name2} share control of the ${color} workers'),
  'placePiece' => clienttranslate('${player_name} places ${piece_name} (${coords})'),
  'moveUp' => clienttranslate('${player_name} moves up to ${level_name} (${coords})'),
  'moveDown' => clienttranslate('${player_name} moves down to ${level_name} (${coords})'),
  'moveOn' => clienttranslate('${player_name} moves on ${level_name} (${coords})'),
  'build' => clienttranslate('${player_name} builds ${piece_name} on ${level_name} (${coords})'),
  'restart' => clienttranslate('${player_name} restarts their turn'),
  'winPlayer' => clienttranslate('${player_name} wins!'),
  'winTeam' => clienttranslate('${player_name} and ${player_name2} win!'),
  'resign' => clienttranslate('${player_name} resigns'),

  'powerGain' => clienttranslate('${player_name} gains power ${power_name}'),
  'powerGainFrom' => clienttranslate('${player_name} gains power ${power_name} from ${player_name2}'),
  'powerDiscard' => clienttranslate('${player_name} discards power ${power_name}'),
  'powerPlacePiece' => clienttranslate('${power_name}: ${player_name} places ${piece_name} (${coords})'),
  'powerMovePiece' => clienttranslate('${power_name}: ${player_name} moves ${piece_name} (${coords})'),
  'powerRemovePiece' => clienttranslate('${power_name}: ${player_name} removes ${piece_name} (${coords})'),
  'powerForce' => clienttranslate('${power_name}: ${player_name} forces ${player_name2} to a space on ${level_name} (${coords})'),
  'powerKill' => clienttranslate('${power_name}: ${player_name} kills ${player_name2} (${coords})'),
  'powerAdditionalTurn' => clienttranslate('${power_name}: ${player_name} may take an additional turn'),
  'powerNoAdditionalTurn' => clienttranslate('${power_name}: ${player_name} may not take an additional turn'),
  'powerNeighboring' => clienttranslate('${power_name}: ${player_name}\'s workers are neighboring'),
  'powerNotNeighboring' => clienttranslate('${power_name}: ${player_name}\'s workers are not neighboring'),
  'powerCompleteTowers' => clienttranslate('${power_name}: ${count} Complete Towers are on the board'),
  'powerDomeBuilt' => clienttranslate('${power_name}: At least one dome was built this turn'),
  'powerAbyss' => clienttranslate('${power_name}: ${player_name2} enters the Abyss (${coords}) and is eliminated!'),
];

$this->specialNames = [
  'ram' => clienttranslate('Golden Fleece power'),
  'nyxNight' => clienttranslate("Nyx's Night Power"),
  'secret' => clienttranslate('secret location'),
];

$this->colorNames = [
  BLUE => clienttranslate('blue'),
  WHITE => clienttranslate('white'),
  PURPLE => clienttranslate('purple'),
];

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
  'tokenCoin' => clienttranslate('a Coin Token'),
  'tokenAbyss' => clienttranslate('the Abyss Token'),
];
