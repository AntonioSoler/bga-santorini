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
  * santorini.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');
require_once('santorini.board.php');

// Terrain constants
define('JUNGLE', 1);
define('GRASS', 2);
define('SAND', 3);
define('ROCK', 4);
define('LAKE', 5);
define('VOLCANO', 6);

// Building constants
define('HUT', 1);
define('TEMPLE', 2);
define('TOWER', 3);

// State constants
define('ST_GAME_BEGIN', 1);
define('ST_NEXT_PLAYER', 2);
define('ST_TILE', 3);
define('ST_ELIMINATE', 5);
define('ST_SELECT_SPACE', 6);
define('ST_BUILDING', 7);
define('ST_GAME_END', 99);

class santorini extends Table
{
    public function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            'selection_x' => 10,
            'selection_y' => 11,
            'selection_z' => 12,
            'variantAllTiles' => 100,
        ));

        $this->tiles = self::getNew('module.common.deck');
        $this->tiles->init('tile');
    }

    protected function getGameName()
    {
        return 'santorini';
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        self::setGameStateInitialValue('selection_x', 0);
        self::setGameStateInitialValue('selection_y', 0);
        self::setGameStateInitialValue('selection_z', 0);

        // Create tiles
        // Distribution from https://boardgamegeek.com/image/155164/santorini
        $tiles = array();
        for ($left = JUNGLE; $left <= LAKE; $left++) {
            for ($right = JUNGLE; $right <= LAKE; $right++) {
                $type = "$left$right";
                $nbr = 1;
                switch ($type) {
                case JUNGLE.GRASS:
                    $nbr = 6;
                    break;

                case GRASS.JUNGLE:
                    $nbr = 5;
                    break;

                case JUNGLE.SAND:
                case SAND.JUNGLE:
                    $nbr = 4;
                    break;

                case JUNGLE.ROCK:
                case JUNGLE.LAKE:
                case GRASS.SAND:
                case GRASS.ROCK:
                case SAND.GRASS:
                case SAND.ROCK:
                case ROCK.JUNGLE:
                case ROCK.GRASS:
                    $nbr = 2;
                    break;
                }
                $tiles[] = array('type' => $type, 'type_arg' => 0, 'nbr' => $nbr);
            }
        }
        $this->tiles->createCards($tiles, 'deck');
        $this->tiles->shuffle('deck');

        // Default variant uses 12 tiles per player
        if (!self::getGameStateValue('variantAllTiles') && count($players) < 4) {
            $this->tiles->pickCardsForLocation(12 * (4 - count($players)), 'deck', 'box');
        }

        // Create players
        self::DbQuery('DELETE FROM player');
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."')";

            // Give each player a tile
            $tile = $this->tiles->pickCard('deck', $player_id);
            $tile['remain'] = $this->tiles->countCardInLocation('deck');
            self::notifyPlayer($player_id, 'draw', '', $tile);
            self::initStat('player', 'tiles', 0, $player_id);
            self::initStat('player', 'buildings_' . HUT, 0, $player_id);
            self::initStat('player', 'buildings_' . TEMPLE, 0, $player_id);
            self::initStat('player', 'buildings_' . TOWER, 0, $player_id);
            self::initStat('player', 'destroy', 0, $player_id);
        }
        self::DbQuery($sql . implode($values, ','));
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();
        self::initStat('table', 'tiles', 0);
        self::initStat('table', 'z', 0);
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $player_id = self::getCurrentPlayerId();
        $board = new santoriniBoard();
        $result = array();
        $result['players'] = $this->getPlayers();
        foreach ($result['players'] as $id => $player) {
            $tile = $this->getTileInHand($id);
            if ($id == $player_id || $id == self::getActivePlayerId()) {
                $result['players'][$id]['preview'] = $tile;
            } elseif ($tile != null) {
                $result['players'][$id]['unknownPreview'] = true;
            }
        }
        $result['terrain'] = $this->terrain;
        $result['spaces'] = $board->getSpaces();
        $result['remain'] = $this->tiles->countCardInLocation('deck');
        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    public function getGameProgression()
    {
        $totalTiles = 48 - $this->tiles->countCardInLocation('box');
        $tileProgress = $this->tiles->countCardInLocation('board') / $totalTiles * 100;
        return round($tileProgress);
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    public function getPlayers()
    {
        return self::getCollectionFromDb('SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, temples, towers, huts FROM player');
    }

    public function getPlayer($player_id)
    {
        return self::getNonEmptyObjectFromDB("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, temples, towers, huts FROM player WHERE player_id = $player_id");
    }

    public function getTileInHand($player_id)
    {
        $t = $this->tiles->getPlayerHand($player_id);
        if (!empty($t)) {
            $t = array_shift($t);
            $tile = array(
                'player_id' => $player_id,
                'tile_id' => (int) $t['id'],
                'tile_type' => $t['type'],
            );
            return $tile;
        }
    }

    public function getPossibleTile()
    {
        $possible = array();
        $board = new santoriniBoard();
        if ($board->empty()) {
            // Center is only possible space at game start
            $possible[] = array('x' => 0, 'y' => 0, 'z' => 1, 'r' => $this->rotations);
        } else {
            $spaces = $board->getSpaces();
            foreach ($spaces as $space) {
                $adjacents = $board->getSpaceAdjacents($space);
                foreach ($adjacents as $as) {
                    if (!$as->exists() && !array_key_exists("$as", $possible)) {
                        $validRotations = array();
                        $rotations = $board->getSpaceRotations($as);
                        foreach ($rotations as $r => $rspaces) {
                            if ($board->isValidTilePlacement($rspaces)) {
                                $validRotations[] = $r;
                            }
                        }
                        $possible["$as"] = array('x' => $as->x, 'y' => $as->y, 'z' => $as->z, 'r' => $validRotations);
                    }

                    $bacons = $board->getSpaceAdjacents($as);
                    foreach ($bacons as $bs) {
                        if (!$bs->exists() && !array_key_exists("$bs", $possible)) {
                            $validRotations = array();
                            $rotations = $board->getSpaceRotations($bs);
                            foreach ($rotations as $r => $rspaces) {
                                if ($board->isValidTilePlacement($rspaces)) {
                                    $validRotations[] = $r;
                                }
                            }
                            $possible["$bs"] = array('x' => $bs->x, 'y' => $bs->y, 'z' => $bs->z, 'r' => $validRotations);
                        }
                    }
                }

                // Check for possible eruptions
                if ($space->face == VOLCANO) {
                    $above = $board->getSpaceAbove($space);
                    if (!$above->exists()) {
                        $validRotations = array();
                        $rotations = $board->getSpaceRotations($above);
                        foreach ($rotations as $r => $rspaces) {
                            $above->r = $r;
                            if ($board->isValidTilePlacement($rspaces)) {
                                $validRotations[] = $r;
                            }
                        }
                        $possible["$above"] = array('x' => $above->x, 'y' => $above->y, 'z' => $above->z, 'r' => $validRotations);
                    }
                }
            }
        }

        // Remove possible spaces with no valid rotations
        $possible = array_filter($possible, function ($item) {
            return !empty($item['r']);
        });

        return array_values($possible);
    }


    public function getPossibleSpaces($player)
    {
        $possible = array();
        $board = new santoriniBoard();
        $spaces = $board->getSpaces();
        foreach ($spaces as $space) {
            if (!empty($board->getBuildingOptions($space, $player))) {
                $possible[] = $space;
            }
        }
        return $possible;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    ////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in santorini.action.php)
    */


    public function actionCommitTile($x, $y, $z, $r)
    {
        $player_id = self::getActivePlayerId();
        $tile = $this->getTileInHand($player_id);
        $tile_id = $tile['tile_id'];
        $board = new santoriniBoard();
        $spaces = $board->getSpacesForTile($x, $y, $z, $r, $tile['tile_type']);
        $valid = $board->isValidTilePlacement($spaces);
        if (!$valid) {
            throw new BgaVisibleSystemException('Invalid tile placement');
        }

        // Add volcano face at the clicked location
        self::DbQuery("INSERT INTO board (x, y, z, r, face, tile_id, subface, tile_player_id) VALUES ($x, $y, $z, $r, " . VOLCANO . ", $tile_id, 0, $player_id) ");
        $board_id = self::DbGetLastId();
        $this->tiles->moveCard($tile['tile_id'], 'board', $board_id);

        // Add secondary faces at adjacent locations
        $values = array(
            1 => "({$spaces[1]->x}, {$spaces[1]->y}, $z, $r, {$spaces[1]->face}, $tile_id, 1, $player_id)",
            2 => "({$spaces[2]->x}, {$spaces[2]->y}, $z, $r, {$spaces[2]->face}, $tile_id, 2, $player_id)",
        );
        self::DbQuery("INSERT INTO board (x, y, z, r, face, tile_id, subface, tile_player_id) VALUES " . implode($values, ','));
        $highest = self::getStat('z');
        if ($z > $highest) {
            self::setStat($z, 'z');
        }

        $player = $this->getPlayer($player_id);
        $tile['i18n'] = array('face_name', 'face_name2');
        $tile['player_name'] = $player['name'];
        $tile['face_name'] = $this->terrain[$spaces[1]->face];
        $tile['face_name2'] = $this->terrain[$spaces[2]->face];
        $tile['x'] = $x;
        $tile['y'] = $y;
        $tile['z'] = $z;
        $tile['r'] = $r;

        // Destroy huts under the tile
        $destroyCount = 0;
        for ($i = 1 ; $i <= 2 ; $i++) {
            $spaceBelow = $board->getSpaceBelow($spaces[$i]);
            if ($spaceBelow->bldg_type == HUT) {
                $destroyCount += $spaceBelow->z;
                self::DbQuery("UPDATE board SET bldg_type = NULL, bldg_player_id = NULL WHERE x = {$spaceBelow->x} AND y = {$spaceBelow->y} AND z = {$spaceBelow->z}");
                self::notifyAllPlayers('destroyBuilding', '', array(
                    'tile_id' => $spaceBelow->tile_id,
                    'subface' => $spaceBelow->subface,
                ));
            }
        }
        if ($destroyCount > 0) {
            self::incStat($destroyCount, 'destroy', $player_id);
        }

        $msg = clienttranslate('${player_name} places a tile with ${face_name} and ${face_name2} on level ${z}');
        if ($destroyCount > 0) {
            $msg = clienttranslate('${player_name} places a tile with ${face_name} and ${face_name2} on level ${z}, destroying ${count} ${bldg_name}.');
            $tile['i18n'][] = 'bldg_name';
            $tile['bldg_name'] = $this->buildings[HUT];
            $tile['count'] = $destroyCount;
        }
        self::notifyAllPlayers('commitTile', $msg, $tile);
        $this->gamestate->nextState('eliminate');
    }

    public function actionSelectSpace($x, $y, $z)
    {
        self::setGameStateValue('selection_x', $x);
        self::setGameStateValue('selection_y', $y);
        self::setGameStateValue('selection_z', $z);
        $this->gamestate->nextState('building');
    }

    public function actionCancel()
    {
        $this->gamestate->nextState('cancel');
    }

    public function actionCommitBuilding($x, $y, $z, $option_nbr)
    {
        $player_id = self::getActivePlayerId();
        $player = $this->getPlayer($player_id);
        $board = new santoriniBoard();
        $space = $board->getSpace($x, $y, $z);
        $options = $board->getBuildingOptions($space, $player);
        if (!array_key_exists($option_nbr, $options)) {
            throw new BgaVisibleSystemException(sprintf('Invalid option: %d', $option_nbr));
        }
        $bldg_type = intdiv($option_nbr, 10);

        // Add buildings
        $buildings = array();
        $count = 0;
        foreach ($options[$option_nbr] as $h) {
            if ($bldg_type == HUT) {
                $count += $h->z;
            } else {
                $count+=1;
            }
            self::DbQuery("UPDATE board SET bldg_player_id = $player_id, bldg_type = $bldg_type WHERE x = {$h->x} AND y = {$h->y} AND z = {$h->z}");
            $h->bldg_player_id = $player_id;
            $h->bldg_type = $bldg_type;
            $buildings[] = $h;
        }

        // Subtract buildings from player
        $bldgName = $this->buildings[$bldg_type];
        $columnName = strtolower($bldgName);
        self::DbQuery("UPDATE player SET $columnName = $columnName - $count WHERE player_id = $player_id AND $columnName >= $count");
        if (self::DbAffectedRow() != 1) {
            throw new BgaVisibleSystemException(sprintf('You do not have enough buildings. This placement requires %d %s.', $count, $bldgName));
        }

        // Increment statistics
        self::incStat($count, 'buildings_' . $bldg_type, $player_id);

        // Update player building counts
        $player = $this->getPlayer($player_id);
        $args = array(
            'i18n' => array('bldg_name', 'face_name'),
            'player_id' => $player_id,
            'player_name' => $player['name'],
            'face_name' => $this->terrain[$space->face],
            'huts' => $player['huts'],
            'temples' => $player['temples'],
            'towers' => $player['towers'],
            'bldg_name' => $bldgName,
            'bldg_type' => $bldg_type,
            'count' => $count,
            'buildings' => $buildings,
        );
        self::notifyAllPlayers('commitBuilding', clienttranslate('${player_name} places ${count} ${bldg_name} on ${face_name}'), $args);

        // Draw next tile
        $newTile = $this->tiles->pickCard('deck', $player_id);
        if ($newTile != null) {
            self::notifyAllPlayers('draw', '', array(
                'player_id' => $player_id,
                'remain' => $this->tiles->countCardInLocation('deck'),
            ));
            self::notifyPlayer($player_id, 'draw', '', array(
                'player_id' => $player_id,
                'tile_id' => $newTile['id'],
                'tile_type' => $newTile['type'],
            ));
        }
        $this->gamestate->nextState('nextPlayer');
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    public function argTile()
    {
        $player_id = self::getActivePlayerId();
        $tile = $this->getTileInHand($player_id);
        $result = $tile;
        $result['possible'] = $this->getPossibleTile();
        return $result;
    }

    public function argBuildingSpaces()
    {
        $player_id = self::getActivePlayerId();
        $player = $this->getPlayer($player_id);
        $result = array(
            'spaces' => $this->getPossibleSpaces($player)
        );
        return $result;
    }

    public function argBuildingTypes()
    {
        $player_id = self::getActivePlayerId();
        $player = $this->getPlayer($player_id);
        $board = new santoriniBoard();
        $space = $board->getSpace(self::getGameStateValue('selection_x'), self::getGameStateValue('selection_y'), self::getGameStateValue('selection_z'));
        $result = array(
            'x' => $space->x,
            'y' => $space->y,
            'z' => $space->z,
            'tile_id' => $space->tile_id,
            'subface' => $space->subface,
            'options' => $board->getBuildingOptions($space, $player),
        );
        return $result;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    public function stNextPlayer()
    {
        $this->activeNextPlayer();

        // You win if you place all of two types of buildings
        $players = $this->getPlayers();
        $weights = array();
        foreach ($players as $player_id => $player) {
            $counts = array(
                $this->buildings[HUT] => $player['huts'],
                $this->buildings[TEMPLE] => $player['temples'],
                $this->buildings[TOWER] => $player['towers'],
            );
            asort($counts);
            $bldg_counts = array_values($counts);
            $bldg_names = array_keys($counts);
            if ($bldg_counts[0] == 0 && $bldg_counts[1] == 0) {
                self::notifyAllPlayers('win', clienttranslate('${player_name} has placed all ${bldg_name} and ${bldg_name2}!'), array(
                    'i18n' => array('bldg_name', 'bldg_name2'),
                    'player_id' => array($player_id),
                    'player_name' => $player['name'],
                    'bldg_name' => $bldg_names[0],
                    'bldg_name2' => $bldg_names[1],
                ));
                self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id = {$player['id']}");
                $this->gamestate->nextState('gameEnd');
                return;
            }
            if (!$player['eliminated'] && !$player['zombie']) {
                // Compute weight of temples > towers > huts (lowest wins)
                $weights[$player_id] = $player['huts'] + $player['towers'] * 100 + $player['temples'] * 1000;
            }
        }

        // You win if you are the only player remaining
        if (count($weights) == 1) {
            reset($weights);
            $player_id = key($weights);
            self::notifyAllPlayers('win', '', array('player_ids' => array($player_id)));
            self::DbQuery("UPDATE player SET player_score = 1 WHERE player_id = $player_id");
            $this->gamestate->nextState('gameEnd');
            return;
        }

        $player_id = self::getActivePlayerId();
        $tile = $this->getTileInHand($player_id);
        if ($tile == null) {
            // You win if you place the most temples, then towers, then huts
            asort($weights);
            $best = reset($weights);
            $winners = array();
            foreach ($weights as $id => $weight) {
                if ($weight == $best) {
                    $winners[] = $id;
                } else {
                    break;
                }
            }
            self::notifyAllPlayers('win', '', array('player_ids' => $winners));
            self::DbQuery('UPDATE player SET player_score = 1 WHERE player_id IN (' . implode(',', $winners) . ')');
            $this->gamestate->nextState('gameEnd');
            return;
        }

        $tile['remain'] = $this->tiles->countCardInLocation('deck');
        self::notifyAllPlayers('draw', '', $tile);
        self::incStat(1, 'tiles');
        self::incStat(1, 'tiles', $player_id);
        self::giveExtraTime($player_id);
        $this->gamestate->nextState('tile');
    }

    public function stEliminate()
    {
        // Check for player elimination
        $player_id = self::getActivePlayerId();
        $player = $this->getPlayer($player_id);
        if (empty($this->getPossibleSpaces($player))) {
            self::notifyAllPlayers('eliminate', clienttranslate('${player_name} cannot place a building!'), array(
                'player_id' => $player_id,
                'player_name' => $player['name'],
            ));
            self::eliminatePlayer($player['id']);
            $this->gamestate->nextState('nextPlayer');
        } else {
            $this->gamestate->nextState('selectSpace');
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    public function zombieTurn($state, $active_player)
    {
        if (array_key_exists('zombiePass', $state['transitions'])) {
            $this->gamestate->nextState('zombiePass');
        } else {
            throw new BgaVisibleSystemException('Zombie player ' . $active_player . ' stuck in unexpected state ' . $state['name']);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    public function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
    }
}
