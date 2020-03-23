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
  * santorini.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */

require_once(APP_GAMEMODULE_PATH.'module/table/table.game.php');

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
            'moved_worker' => 13,
            'variant_powers' => 100,
        ));

        $this->pieces = self::getNew('module.common.deck');
        $this->pieces->init('piece');
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
        self::setGameStateInitialValue('moved_worker', 0);

        // Init pieces
        $pieces = array();
        $pieces[] = array('type' => 'worker_blue', 'type_arg' => 0, 'nbr' => 1);
        $pieces[] = array('type' => 'worker_white', 'type_arg' => 0, 'nbr' => 1);
		$pieces[] = array('type' => 'worker_blue', 'type_arg' => 1, 'nbr' => 1);
        $pieces[] = array('type' => 'worker_white', 'type_arg' => 1, 'nbr' => 1);
        $pieces[] = array('type' => 'dome', 'type_arg' => 0, 'nbr' => 18);
        $pieces[] = array('type' => 'level1', 'type_arg' => 0, 'nbr' => 22);
        $pieces[] = array('type' => 'level2', 'type_arg' => 0, 'nbr' => 18);
        $pieces[] = array('type' => 'level3', 'type_arg' => 0, 'nbr' => 14);
        $this->pieces->createCards($pieces, 'deck', 0);

        // Init board (tridimensional 5x5x4)
        $sql = 'INSERT INTO board (x, y, z) VALUES ';
        $values = array();
        for ($x = 0; $x < 5; $x++) {
            for ($y = 0; $y < 5; $y++) {
                for ($z = 0; $z < 4; $z++) {
                    $values[] = "('$x','$y','$z')";
                }
            }
        }
        self::DbQuery($sql . implode($values, ','));

        // Create players
        self::DbQuery('DELETE FROM player');
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes($player['player_name'])."','".addslashes($player['player_avatar'])."')";

            $workers = $this->pieces->getCardsOfType('worker_' . ($color == 'ffffff' ? 'white' : 'blue'));
            $this->pieces->moveCards(array_keys($workers), 'deck', $player_id);
        }
        self::DbQuery($sql . implode($values, ','));
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        // Active first player to play
        $this->activeNextPlayer();
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
        $result = array();
        $result['players'] = $this->getPlayers();
        $result['spaces'] = $this->getSpaces();
        $result['placed_pieces'] = $this->getPlacedPieces();
        $result['available_pieces'] = $this->getAvailablePieces();
        $result['moved_worker'] = self::getGamestateValue('moved_worker');
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
        // Number of pieces on the board / total number of pieces
        $nbr_placed = count(self::getPlacedPieces());
        $nbr_available = count(self::getAvailablePieces());
        
        return $nbr_placed / ($nbr_placed+$nbr_available);
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////

    public function getPlayers()
    {
        return self::getCollectionFromDb("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated FROM player");
    }

    public function getPlayer($player_id)
    {
        return self::getNonEmptyObjectFromDB("SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated FROM player WHERE player_id = $player_id");
    }

    public function getPlacedPieces()
    {
        return $this->pieces->getCardsInLocation('board');
    }

    public function getAvailablePieces()
    {
        return $this->pieces->getCardsInLocation('deck');
    }

    public function getSpaces()
    {
        return self::getCollectionFromDb('SELECT space_id, x, y, z, piece_id FROM board ORDER BY x, y, z');
    }

    public function getAccessibleSpaces()
    {
        $unoccupied =  self::getCollectionFromDb('SELECT space_id, x, y, z, piece_id FROM board WHERE piece_id is null ORDER BY x ASC, y ASC, z ASC');

        $accessible = array();
        $x = null;
        $y = null;
        foreach( $unoccupied as $space_id => $space ) {
            // Accessible = first going up on the z_axis among unnocupied spaces correctly sorted
            if ($space['x'] !== $x || $space['y'] !== $y) {
                $accessible[$space_id] = $space;
                $x = $space['x'];
                $y = $space['y'];
            }
        }

        return $accessible;
    }

    public function getNeighbouringSpaces($worker_id, $formoving=false)
    {
        $worker_space = self::getNonEmptyObjectFromDb("SELECT space_id, x, y, z, piece_id FROM board WHERE piece_id = '$worker_id'");
        $x = $worker_space['x'];
        $y = $worker_space['y'];
        $z = $worker_space['z'];

        //throw new BgaUserException(print_r($worker_id, true));
        //throw new BgaUserException(print_r($formoving, true));

        $accessible = self::getAccessibleSpaces();

        $neighbouring = array();
        foreach( $accessible as $space_id => $space ) {
            // Neighbouring = 1 planar coordinate distant / height for moving is only one step when going upwards
            if (($x != $space['x'] || $y != $space['y'])
                    && abs($x - $space['x']) <= 1
                    && abs($y - $space['y']) <= 1
                    && (!$formoving || $space['z'] - $z <= 1)) {
                $neighbouring[$space_id] = $space;
            }
        }

        //throw new BgaUserException(print_r($neighbouring, true));

        return $neighbouring;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    ////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in santorini.action.php)
    */

    public function place($x, $y, $z)
    {
        self::checkAction('place');

        $player_id = self::getActivePlayerId();

        // Get unplaced workers for the active player
        $workers = $this->pieces->getCardsInLocation('deck', $player_id);

        if (count($workers) == 0) {
            throw new BgaVisibleSystemException( 'No more workers to place' );
        }
        $space_id = self::getUniqueValueFromDb( "SELECT space_id FROM board WHERE x = '$x' AND y = '$y' AND z = '$z' AND piece_id is null" );
        if ($space_id === null) {
            throw new BgaUserException( _("This space is not free") );
        }
        if ($z > 0) {
            throw new BgaVisibleSystemException( 'Worker placed higher than ground floor' );
        }
        
        // Place one worker in this space
        $worker = array_shift($workers);
        $worker_id = $worker['id'];

        self::DbQuery( "UPDATE board SET piece_id = '$worker_id' WHERE x = '$x' AND y = '$y' AND z = '$z'" );

        $this->pieces->moveCard($worker_id, 'board', $player_id);

        // Notify
        $args = array(
            'i18n' => array(),
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'worker_id' => $worker_id,
            'space_id' => $space_id,
        );
        self::notifyAllPlayers('workerPlaced', clienttranslate('${player_name} places a worker'), $args);

        $this->gamestate->nextState('placed');
    }

    public function move($worker_id, $x, $y, $z)
    {
        self::checkAction('move');

        $player_id = self::getActivePlayerId();

        // Get workers for the active player
        $workers = $this->pieces->getCardsInLocation('board', $player_id);

        if (!in_array($worker_id, array_keys($workers))) {
            throw new BgaUserException( _("This worker is not yours") );
        }
        $space_id = self::getUniqueValueFromDb( "SELECT space_id FROM board WHERE x = '$x' AND y = '$y' AND z = '$z' AND piece_id is null" );
        if ($space_id === null) {
            throw new BgaUserException( _("This space is not free") );
        }
        //throw new BgaUserException(print_r($worker_id, true));
        $neighbouring = self::getNeighbouringSpaces($worker_id, true);
        //throw new BgaUserException(print_r($neighbouring, true));
        if (!in_array($space_id, array_keys($neighbouring))) {
            throw new BgaUserException( _("You cannot reach this space with this worker") );
        }

        // Move worker
        self::DbQuery( "UPDATE board SET piece_id = null WHERE piece_id = '$worker_id'" );
        self::DbQuery( "UPDATE board SET piece_id = '$worker_id' WHERE x = '$x' AND y = '$y' AND z = '$z'" );

        // Set moved worker
        self::setGamestateValue( 'moved_worker', $worker_id );

        // Notify
        $args = array(
            'i18n' => array(),
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'worker_id' => $worker_id,
            'space_id' => $space_id,
        );
        self::notifyAllPlayers('workerMoved', clienttranslate('${player_name} moves a worker'), $args);

        $this->gamestate->nextState('moved');
    }

    public function build($x, $y, $z)
    {
        self::checkAction('build');

        $player_id = self::getActivePlayerId();
        $worker_id = self::getGamestateValue( 'moved_worker' );

        $space_id = self::getUniqueValueFromDb( "SELECT space_id FROM board WHERE x = '$x' AND y = '$y' AND z = '$z' AND piece_id is null" );
        if ($space_id === null) {
            throw new BgaUserException( _("This space is not free") );
        }
        $neighbouring = self::getNeighbouringSpaces($worker_id);
        if (!in_array($space_id, array_keys($neighbouring))) {
            throw new BgaUserException( _("This space is not neighbouring the moved worker") );
        }

        $type = ($z === 0 ? 'level1' : ($z === 1 ? 'level2' : ($z === 2 ? 'level3' : 'dome')));
        $blocks = $this->pieces->getCardsOfTypeInLocation($type, null, 'deck', null);
        if (count($blocks) == 0) {
            throw new BgaUserException( _("No more blocks for building at this level") );
        }
        $block = array_shift($blocks);
        $block_id = $block['id'];

        self::DbQuery( "UPDATE board SET piece_id = '$block_id' WHERE x = '$x' AND y = '$y' AND z = '$z'" );
		self::DbQuery( "UPDATE piece SET card_location = 'board' WHERE card_id = $block_id " );

        // Reset moved worker
        self::setGamestateValue( 'moved_worker', 0 );

        // Notify
        $args = array(
            'i18n' => array(),
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'block' => $block,
            'space_id' => $space_id,
            'level' => $z
        );
        $msg = clienttranslate('${player_name} builds at ground level');
        if ($z > 0) $msg = clienttranslate('${player_name} builds at level ${level}');
        self::notifyAllPlayers('blockBuilt', $msg, $args);

        $this->gamestate->nextState('built');
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    public function argPlaceWorker()
    {
        $player_id = self::getActivePlayerId();

        // Return unoccupied spaces that are accessible
        $result = array( 'accessible_spaces' => self::getAccessibleSpaces() );
        return $result;
    }

    public function argPlayerMove()
    {
        $player_id = self::getActivePlayerId();

        // Return for each worker of this player the spaces he can move to
        $workers = $this->pieces->getCardsInLocation('board', self::getActivePlayerId());

        $destinations = array();
        foreach ($workers as $worker_id => $worker) {
            $destinations[$worker_id] = self::getNeighbouringSpaces($worker_id);
        }
        
        $result = array( 'destinations_by_worker' => $destinations );
        return $result;
    }

    public function argPlayerBuild()
    {
        $player_id = self::getActivePlayerId();

        // Return available spaces neighbouring the moved player
        $worker_id = self::getGamestateValue('moved_worker');
        
        $result = array( 'neighbouring_spaces' => self::getNeighbouringSpaces($worker_id) );
        return $result;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    public function stNextPlayerPlaceWorker()
    {
        $player_id = self::getActivePlayerId();
        
        // Get unplaced workers for the active player
        $workers = $this->pieces->getCardsInLocation('deck', $player_id);

        if (count($workers) > 0) {
            // It's still this player turn, he has to place both workers
        } else {
            // Move on to the other player
            $player_id = $this->activeNextPlayer();
            $workers = $this->pieces->getCardsInLocation('deck', $player_id);
        }

        if (count($workers) > 0) {
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('next');
        } else {
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('done');
        }
    }

    public function stNextPlayer()
    {
        $player_id = $this->activeNextPlayer();

        self::giveExtraTime($player_id);
        
        $this->gamestate->nextState('next');
    }

    public function stCheckEndOfGame()
    {
        // TODO: active player reached level 3 or active player cannot move or active player cannot build
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
