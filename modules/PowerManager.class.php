<?php

// TODO : description
class PowerManager extends APP_GameClass
{
  public $game;

  public function __construct($game)
  {
    $this->game = $game;
  }

  /* Factory function to create a power by ID */
  public function getPower($powerId, $playerId = null) {
    if(!isset(self::$powersClasses[$powerId])) {
      throw new BgaVisibleSystemException( "Power $powerId is not implemented" );
    }
    return new self::$powersClasses[$powerId]($this->game, $playerId);
  }

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
   * Get possible powers:
   *   TODO
   * params: TODO
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
   * dividePowers: TODO
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
   * choosePower: TODO
   */
  public function choosePower($id, $pId = null)
  {
    $pId = $pId ?: $this->game->getActivePlayerId();
    $this->game->playerManager->getPlayer($pId)->addPower($id);
  }


/*
  public function setup($player) {}

  public function beforeMove() {}

  public function beforeBuild() {}
  public function argBuild() {}
  public function build() {}
  public function endTurn() {}
  public function winCondition() {}
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



  public function argPlayerMove(&$arg)
  {
    $this->argPlayerWork($arg, 'Move');
  }

  public function argPlayerBuild(&$arg)
  {
    $this->argPlayerWork($arg, 'Build');
  }



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


  public function playerMove($worker, $work)
  {
    return $this->playerWork($worker, $work, 'Move');
  }

  public function playerBuild($worker, $work)
  {
    return $this->playerWork($worker, $work, 'Build');
  }



  public function stateAfterWork($action)
  {
    $name = "stateAfter".$action;
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    $r = array_filter(array_map(function($power) use ($name) {
      return $power->$name();
    }, $player->getPowers()));
    if(count($r) > 1)
      throw new BgaUserException(_("Can't figure next state after action"));

    if(count($r) == 1)
      return $r[0];
    else
      return null;
  }


  public function stateAfterMove()
  {
    return $this->stateAfterWork('Move');
  }

  public function stateAfterBuild()
  {
    return $this->stateAfterWork('Build');
  }
}
