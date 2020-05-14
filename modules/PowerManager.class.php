<?php

/*
 * PowerManager : allow to easily create and apply powers during play
 */
class PowerManager extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }

  /*
   * getPower: factory function to create a power by ID
   */
  public function getPower($powerId, $playerId = null) {
    if(!isset(self::$powersClasses[$powerId])) {
      throw new BgaVisibleSystemException( "Power $powerId is not implemented" );
    }
    return new self::$powersClasses[$powerId]($this->game, $playerId);
  }

  /*
   * powerClasses : for each power Id, the corresponding class name
   *  (see also constant.inc.php)
   */
  public static $powersClasses = [
    APOLLO => 'Apollo',
    ARTEMIS => 'Artemis',
    ATHENA => 'Athena',
    ATLAS => 'Atlas',
    DEMETER => 'Demeter',
    HEPHAESTUS => 'Hephaestus',
    HERMES => 'Hermes',
    MINOTAUR => 'Minotaur',
    PAN => 'Pan',
    PROMETHEUS => 'Prometheus',
    APHRODITE => 'Aphrodite',
    ARES => 'Ares',
    BIA => 'Bia',
    CHAOS => 'Chaos',
    CHARON => 'Charon',
    CHRONUS => 'Chronus',
    CIRCE => 'Circe',
    DIONYSUS => 'Dionysus',
    EROS => 'Eros',
    HERA => 'Hera',
    HESTIA => 'Hestia',
    HYPNUS => 'Hypnus',
    LIMUS => 'Limus',
    MEDUSA => 'Medusa',
    MORPHEUS => 'Morpheus',
    PERSEPHONE => 'Persephone',
    POSEIDON => 'Poseidon',
    SELENE => 'Selene',
    TRITON => 'Triton',
    ZEUS => 'Zeus',
    AEOLUS => 'Aeolus',
    CHARYBDIS => 'Charybdis',
    CLIO => 'Clio',
    EUROPA => 'Europa',
    GAEA => 'Gaea',
    GRAEAE => 'Graeae',
    HADES => 'Hades',
    HARPIES => 'Harpies',
    HECATE => 'Hecate',
    MOERAE => 'Moerae',
    NEMESIS => 'Nemesis',
    SIREN => 'Siren',
    TARTARUS => 'Tartarus',
    TERPSICHORE => 'Terpsichore',
    URANIA => 'Urania',
    ACHILLES => 'Achilles',
    ADONIS => 'Adonis',
    ATALANTA => 'Atalanta',
    BELLEROPHON => 'Bellerophon',
    HERACLES => 'Heracles',
    JASON => 'Jason',
    MEDEA => 'Medea',
    ODYSSEUS => 'Odysseus',
    POLYPHEMUS => 'Polyphemus',
    THESEUS => 'Theseus',
  ];

  /*
   * Get playable powers: given the game option, return the list of playable power for this game
   */
  public function getPlayablePowers()
  {
    $optionPowers = intval($this->game->getGameStateValue('optionPowers'));
    if ($optionPowers == NONE) {
      return [];
    }

    // Gather information about number of players
    $nPlayers = $this->game->playerManager->getPlayerCount();

    // Filter powers depending on the number of players and game option
    return array_filter($this->game->powers, function ($power, $id) use ($nPlayers, $optionPowers) {
      return in_array($nPlayers, $power['players']) &&
        (($optionPowers == SIMPLE && $id <= 10)
          || ($optionPowers == GODS && !$power['hero'])
          || ($optionPowers == HEROES && $power['hero'])
          || ($optionPowers == GODS_AND_HEROES)
          || ($optionPowers == GOLDEN_FLEECE && $power['golden']));
    }, ARRAY_FILTER_USE_BOTH);
  }


  /*
   * getPowersInLocation: return all the power cards in a given location
   */
  public function getPowersInLocation($location)
  {
    $cards = $this->game->cards->getCardsInLocation($location);
    $powers = array_map(function($card) {
      return $this->game->powers[$card['type']];
    }, $cards);

    return array_values($powers);
  }


  /*
   * dividePowers: is called after the contestant has choosed the list of powers
   *    that will be used during this game. We put these power into the stack in
   *    order to make them available for choosePower action.
   */
  public function dividePowers($ids)
  {
    // Move selected powers to stack
    $this->game->cards->moveCards($ids, 'stack');

    // Notify other players
    $powers = array_map(function($id){ return $this->game->powers[$id]['name']; }, $ids);
    $args = [
      'i18n' => [],
      'powers_names' => implode(', ', $powers),
      'player_name' => $this->game->getActivePlayerName(),
    ];
    $this->game->notifyAllPlayers('powersDivided', clienttranslate('${player_name} selects ${powers_names}'), $args);
  }


  /*
   * choosePower: is called after a player has choosed a power from the stack.
   */
  public function choosePower($id, $pId = null)
  {
    $pId = $pId ?: $this->game->getActivePlayerId();
    $this->game->playerManager->getPlayer($pId)->addPower($id);
  }


///////////////////////////////////////
///////////////////////////////////////
/////////    Work argument   //////////
///////////////////////////////////////
///////////////////////////////////////

  /*
   * argPlayerWork: is called whenever a player is going to do some work (move/build)
   *    apply every player powers that may add new works or make the work skippable
   *    and then apply every opponent powers that may restrict the possible works
   */
  public function argPlayerWork(&$arg, $action)
  {
    // First apply current user power(s)
    $name = "argPlayer".$action;
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    foreach($player->getPowers() as $power)
      $power->$name($arg);

    // Then apply oponnents power(s)
    $name = "argOpponent".$action;
    foreach($this->game->playerManager->getOpponents($pId) as $opponent)
    foreach($opponent->getPowers() as $power)
      $power->$name($arg);
  }


  /*
   * argPlayerMove: is called whenever a player is going to do some move
   */
  public function argPlayerMove(&$arg)
  {
    $this->argPlayerWork($arg, 'Move');
  }

  /*
   * argPlayerBuild: is called whenever a player is going to do some build
   */
  public function argPlayerBuild(&$arg)
  {
    $this->argPlayerWork($arg, 'Build');
  }



/////////////////////////////////////
/////////////////////////////////////
/////////    Work action   //////////
/////////////////////////////////////
/////////////////////////////////////

  /*
   * argPlayerWork: is called whenever a player try to do some work (move/build).
   *    This is called after checking that the work is valid using argPlayerWork.
   *    This should return true if we want to bypass the usual work function:
   *      eg, Appolo can 'switch' instead of 'move'
   */
  public function playerWork($worker, $work, $action)
  {
    // First apply current user power(s)
    $name = "player".$action;
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    $r = array_map(function($power) use ($worker, $work, $name){
      return $power->$name($worker, $work);
    }, $player->getPowers());
    return max($r);

    // TODO use an opponentMove function ?
  }


  /*
   * playerMove: is called whenever a player is moving
   */
  public function playerMove($worker, $work)
  {
    return $this->playerWork($worker, $work, 'Move');
  }


  /*
   * playerBuild: is called whenever a player is building
   */
  public function playerBuild($worker, $work)
  {
    return $this->playerWork($worker, $work, 'Build');
  }



/////////////////////////////////////
/////////////////////////////////////
////////   AfterWork state   ////////
/////////////////////////////////////
/////////////////////////////////////

  /*
   * getNewState: is called whenever we try to get the new state
   *   - after a work / skip
   *   - at the beggining of the turn
   */
  public function getNewState($method, $msg)
  {
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    $r = array_filter(array_map(function($power) use ($method) {
      return $power->$method();
    }, $player->getPowers()));
    if(count($r) > 1)
      throw new BgaUserException($msg);

    if(count($r) == 1)
      return $r[0];
    else
      return null;
  }


  /*
   * stateAfterWork: is called whenever a player has done some work (in a regular way).
   *    This should return null if we want to continue as usual,
   *      or a valid transition name if we want something special.
   */
  public function stateAfterWork($action)
  {
    $name = "stateAfter".$action;
    return $this->getNewState($name, _("Can't figure next state after action"));
  }

  /*
   * stateAfterMove: is called after a regular move
   */
  public function stateAfterMove()
  {
    return $this->stateAfterWork('Move');
  }

  /*
   * stateAfterBuild: is called after a regular build
   */
  public function stateAfterBuild()
  {
    return $this->stateAfterWork('Build');
  }

/////////////////////////////////////
/////////////////////////////////////
///////  Start/end turn state  //////
/////////////////////////////////////
/////////////////////////////////////

  /*
   * stateStartTurn: is called at the beginning of the player state.
   */
  public function stateStartTurn()
  {
    return $this->getNewState('stateStartTurn', _("Can't figure next state at the beginning of the turn"));
  }

  /*
   * stateAfterSkip: is called after a skip
   */
  public function stateAfterSkip()
  {
    return $this->getNewState('stateAfterSkip', _("Can't figure next state after a skip"));
  }



/////////////////////////////////////
/////////////////////////////////////
///////////    Winning    ///////////
/////////////////////////////////////
/////////////////////////////////////

  /*
   * checkWinning: is called after each work.
   *    $arg contains info about whether some player is winning,
   *      and what should the message be in case of win
   *    We first apply current player power that may make it win
   *      with some additionnal winning condition (eg Pan).
   *   Then we apply opponents powers that may do two think:
   *     - restrict a win : eg Aphrodite or Pegasus
   *     - steal a win : eg Moerae
   *     - make an opponent win : eg Chronus
   */
  public function checkWinning(&$arg)
  {
    // First apply current user power(s)
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    foreach($player->getPowers() as $power)
      $power->checkPlayerWinning($arg);

    // Then apply oponnents power(s)
    foreach($this->game->playerManager->getOpponents($pId) as $opponent)
    foreach($opponent->getPowers() as $power)
      $power->checkOpponentWinning($arg);
  }

}
