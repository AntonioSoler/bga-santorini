<?php

/*
 * PowerManager : allow to easily create and apply powers during play
 */
class PowerManager extends APP_GameClass
{
  /*
   * powerClasses : for each power Id, the corresponding class name
   *  (see also constant.inc.php)
   */
  public static $classes = [
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
    TYCHE => 'Tyche',
    SCYLLA => 'Scylla',
    CASTOR => 'Castor',
    PROTEUS => 'Proteus',
    ERIS => 'Eris',
    MAENADS => 'Maenads',
    ASTERIA => 'Asteria',
    HIPPOLYTA => 'Hippolyta',
    HYDRA => 'Hydra',
    IRIS => 'Iris',
    NYX => 'Nyx',
    PEGASUS => 'Pegasus',
  ];

  /*
   * TODO
   */
  public static $bannedMatchups = [
    [APHRODITE, BIA], // https://boardgamearena.com/bug?id=21093
    [APHRODITE, MEDUSA], // https://boardgamearena.com/bug?id=21093
    [APHRODITE, NEMESIS],
    [APHRODITE, THESEUS], // https://boardgamearena.com/bug?id=21093
    [APHRODITE, URANIA],
    [ASTERIA, HADES],
    [ATLAS, GAEA],
    [BIA, NEMESIS],
    [BIA, TARTARUS],
    [CHARON, HECATE],
    [CIRCE, CLIO],
    [CIRCE, EROS],
    [CIRCE, GAEA],
    [CIRCE, GRAEAE],
    [CIRCE, HECATE],
    [CIRCE, MOERAE],
    [CIRCE, NYX],
    [CIRCE, PROTEUS],
    [CIRCE, TARTARUS],
    [CLIO, NEMESIS],
    [ERIS, HECATE],
    [ERIS, TARTARUS],
    [GAEA, NEMESIS],
    [GAEA, SELENE],
    [GRAEAE, NEMESIS],
    [HADES, PAN],
    [HARPIES, HERMES],
    [HARPIES, MAENADS],
    [HARPIES, TRITON],
    [HECATE, DIONYSUS],
    [HECATE, MEDEA],
    [HECATE, MOERAE],
    [HECATE, NYX],
    [HECATE, SCYLLA],
    [HECATE, TARTARUS],
    [HECATE, THESEUS],
    [HYPNUS, TERPSICHORE],
    [LIMUS, TERPSICHORE],
    [MEDUSA, NEMESIS],
    [MOERAE, NEMESIS],
    [MOERAE, TARTARUS],
    [NEMESIS, TERPSICHORE],
    [NEMESIS, THESEUS],
    [SELENE, GAEA],
    [TARTARUS, TERPSICHORE],

    // Circe ban all heroes
    [CIRCE, ACHILLES],
    [CIRCE, ADONIS],
    [CIRCE, ATALANTA],
    [CIRCE, BELLEROPHON],
    [CIRCE, HERACLES],
    [CIRCE, JASON],
    [CIRCE, MEDEA],
    [CIRCE, ODYSSEUS],
    [CIRCE, POLYPHEMUS],
    [CIRCE, THESEUS],

    // Incomplete Persephone implementation
    [PERSEPHONE, ARTEMIS],  // multiple moves
    [PERSEPHONE, ATALANTA], // multiple moves
    [PERSEPHONE, BELLEROPHON], // cannot require use of hero power
    [PERSEPHONE, CASTOR], // alternative turn, https://boardgamearena.com/bug?id=22350
    [PERSEPHONE, CHARON],  // CF PERSEPHONE
    [PERSEPHONE, CHARYBDIS], // complex moves
    [PERSEPHONE, ERIS], // rulebook & alternative turn
    [PERSEPHONE, HERMES], // multiple moves
    [PERSEPHONE, HIPPOLYTA], // https://boardgamearena.com/bug?id=20286
    [PERSEPHONE, JASON], // alternative turn
    [PERSEPHONE, PROMETHEUS],  // CF PERSEPHONE
    [PERSEPHONE, SIREN], // alternative turn
    [PERSEPHONE, TERPSICHORE], // both workers must move
    [PERSEPHONE, TRITON], // multiple moves

    // Incomplete Adonis implementation
    [ADONIS, ARTEMIS], // multiple moves
    [ADONIS, ATALANTA], // multiple moves
    [ADONIS, BELLEROPHON], // cannot require use of hero power
    [ADONIS, CASTOR], // alternative turn
    [ADONIS, CHARON],  // pre-chosen worker
    [ADONIS, CHARYBDIS], // complex moves
    [ADONIS, ERIS], // alternative turn
    [ADONIS, HERMES], // multiple moves
    [ADONIS, HIPPOLYTA], // https://boardgamearena.com/bug?id=20286
    [ADONIS, JASON], // alternative turn
    [ADONIS, PROMETHEUS],  // movement restriction
    [ADONIS, SIREN], // alternative turn
    [ADONIS, TERPSICHORE], // both workers must move
    [ADONIS, TRITON], // multiple moves
  ];


  public $game;
  public $cards;
  public function __construct($game)
  {
    $this->game = $game;

    // stats.inc.php creates PowerManager without a game object
    if ($game != null) {
      // Initialize power deck
      $this->cards = self::getNew('module.common.deck');
      $this->cards->init('card');
      $this->cards->autoreshuffle = true;
      $this->cards->autoreshuffle_custom = [
        'deck' => 'discard',
        'tycheDeck' => 'tycheDiscard',
      ];
    }
  }

  /*
   * getPower: factory function to create a power by ID
   */
  public function getPower($powerId, $playerId = null)
  {
    if (!isset(self::$classes[$powerId])) {
      throw new BgaVisibleSystemException("getPower: Unknown power $powerId (player: $playerId)");
    }
    return new self::$classes[$powerId]($this->game, $playerId);
  }

  /*
   * getPowers: return all powers (even those not available in this game)
   */
  public function getPowers()
  {
    $playerMap = [];
    if ($this->cards != null) {
      $cards = $this->cards->getCardsInLocation('hand');
      foreach ($cards as $card) {
        $playerMap[$card['type']] = intval($card['location_arg']);
      }
    }
    return array_map(function ($powerId) use ($playerMap) {
      $playerId = array_key_exists(strval($powerId), $playerMap) ? $playerMap[strval($powerId)] : null;
      return $this->getPower($powerId, $playerId);
    }, array_keys(self::$classes));
  }

  /*
   * getUiData : get all ui data of all powers : id, name, title, text, hero
   */
  public function getUiData()
  {
    $ui = [];
    foreach ($this->getPowers() as $power) {
      if ($power->isImplemented()) {
        $ui[$power->getId()] = $power->getUiData();
      }
    }
    return $ui;
  }

  public function getStatLabels()
  {
    $labels = [
      0 => ''
    ];
    foreach ($this->getPowers() as $power) {
      $labels[$power->getId()] = $power->getName();
    }
    return $labels;
  }

  /*
   * getPowersInLocation: return all the powers in a given location
   */
  public function getPowersInLocation($location, $locationArg = null)
  {
    return array_values(array_map(function ($powerId) {
      return $this->getPower($powerId);
    }, $this->getPowerIdsInLocation($location, $locationArg)));
  }

  /*
   * getPowerIdsInLocation: return all the power IDs in a given location
   */
  public function getPowerIdsInLocation($location, $locationArg = null)
  {
    $cards = $this->cards->getCardsInLocation($location, $locationArg);
    return array_values(array_map(function ($card) {
      return intval($card['type']);
    }, $cards));
  }

  /*
   * getOpponentPowerIds: return all the power IDs for opponent players
   */
  public function getOpponentPowerIds($pId = -1)
  {
    $powerIds = [];
    foreach ($this->game->playerManager->getOpponentsIds() as $opponentId) {
      $powerIds = array_merge($powerIds, $this->getPowerIdsInLocation('hand', $opponentId));
    }
    return $powerIds;
  }

  /*
   * hasPower: return true if the power ID is currently in the player's hand
   */
  public function hasPower($powerId, $playerId = -1)
  {
    $playerId = $playerId == -1 ? $this->game->getActivePlayerId() : $playerId;
    return in_array($powerId, $this->getPowerIdsInLocation('hand', $playerId));
  }

  /*
   * createCards:
   *   during game setup, create power card
   */
  public function createCards()
  {
    $sql = 'INSERT INTO card (card_type, card_type_arg, card_location, card_location_arg) VALUES ';
    $values = [];
    foreach (array_keys(self::$classes) as $powerId) {
      $values[] = "('$powerId', 0, 'box', 0)";
    }
    self::DbQuery($sql . implode(',', $values));
  }

  /*
   * preparePowers: move supported power cards to the deck
   */
  public function preparePowers()
  {
    $optionPowers = intval($this->game->getGameStateValue('optionPowers'));
    if ($optionPowers == NONE) {
      return 'placeWorker';
    }

    // Filter supported powers depending on the number of players and game option
    $nPlayers = $this->game->playerManager->getPlayerCount();
    $powers = array_filter($this->getPowers(), function ($power) use ($nPlayers, $optionPowers) {
      return $power->isSupported($nPlayers, $optionPowers);
    });
    $powerIds = array_values(array_map(function ($power) {
      return $power->getId();
    }, $powers));

    // Additional filtering for QUICK and TOURNAMENT
    $optionSetup = intval($this->game->getGameStateValue('optionSetup'));
    if ($optionPowers == GODS_AND_HEROES && $optionSetup == QUICK) {
      // Fix invalid gameoptions at runtime
      // https://boardgamearena.com/bug?id=21524
      $optionSetup = TOURNAMENT;
    }
    if (($optionSetup == QUICK || $optionSetup == TOURNAMENT)) {
      $count = $optionSetup == QUICK ? ($optionPowers == GOLDEN_FLEECE ? 1 : $nPlayers) : ($nPlayers + 1) * 2;
      if (count($powerIds) < $count) {
        throw new BgaVisibleSystemException("preparePowers: Not enough powers available (expected: $count, actual: " . count($powerIds) . ")");
      }
      $offer = [];
      for ($i = 0; $i < $count; $i++) {
        $offer[] = $powerIds[array_rand($powerIds, 1)];
        Utils::filter($powerIds, function ($power) use ($offer) {
          // Remove the selected powers AND any banned powers
          return !in_array($power, $offer) && !in_array($power, $this->computeBannedIds($offer));
        });
      }
      $powerIds = $offer;
      if (count($powerIds) != $count) {
        throw new BgaVisibleSystemException("preparePowers: Wrong number of powers (expected: $count, actual: " . count($powerIds) . ")");
      }
    }

    if ($optionSetup == QUICK && $optionPowers == GOLDEN_FLEECE) {
      // QUICK: Go to place worker
      $this->prepareGoldenFleece($powerIds[0]);
      return 'placeWorker';
    } else if ($optionSetup == QUICK) {
      // QUICK: Skip building offer
      $this->cards->moveCards($powerIds, 'offer');
      return 'chooseFirstPlayer';
    } else {
      // TOURNAMENT and CUSTOM: Build offer
      $this->cards->moveCards($powerIds, 'deck');
      $this->cards->shuffle('deck');
      return 'offer';
    }
  }


  /*
   * computeBannedIds: is called during fair division setup, whenever a player add/remove an offer
   *    it should return the list of banned powers against current offer
   */
  public function computeBannedIds($mixed = 'offer')
  {
    $powers = is_array($mixed) ? $mixed : $this->getPowerIdsInLocation($mixed);
    $ids = [];
    foreach ($powers as $power) {
      foreach (self::$bannedMatchups as $matchup) {
        if ($matchup[0] == $power) {
          $ids[] = $matchup[1];
        }
        if ($matchup[1] == $power) {
          $ids[] = $matchup[0];
        }
      }
    }
    return $ids;
  }


  /*
   * addOffer:
   *   during fair division setup, player 1 adds a power to the offer
   */
  public function addOffer($powerId)
  {
    // Move the power card to the selection
    $this->cards->moveCard($powerId, 'offer');
    $this->game->notifyAllPlayers('addOffer', '', [
      'powerId' => $powerId,
      'banned' => $this->computeBannedIds()
    ]);
  }

  /*
   * removeOffer:
   *   during fair division setup, player 1 remove a power from the offer
   */
  public function removeOffer($powerId)
  {
    // Move the power card to the deck
    $this->cards->moveCard($powerId, 'deck');
    $this->game->notifyAllPlayers('removeOffer', '', [
      'powerId' => $powerId,
      'banned' => $this->computeBannedIds()
    ]);
  }


  /*
   * getFirstPlayerSuggestion: TODO
   */
  public function getFirstPlayerSuggestion($offer)
  {
    $minOrderAid = 100;
    $minPowerId = 0;
    foreach ($offer as $powerId) {
      $power = $this->getPower($powerId);
      $o = $power->getOrderAid();
      if ($o < $minOrderAid && $o >= 0) {
        $minOrderAid = $o;
        $minPowerId = $powerId;
      }
    }

    return $minPowerId;
  }


  /*
   * setFirstPlayerOffer: set which power will start
   */
  public function setFirstPlayerOffer($powerId)
  {
    $this->cards->moveCard($powerId, 'offer', '1');
  }


  /*
   * getOffer: return all the offer
   */
  public function getOffer()
  {
    return array_values($this->cards->getCardsInLocation('offer'));
  }



  ///////////////////////////////////////
  ///////////////////////////////////////
  /////////    Golden Fleece ////////////
  ///////////////////////////////////////
  ///////////////////////////////////////

  public function isGoldenFleece()
  {
    return intval($this->game->getGameStateValue('optionPowers')) == GOLDEN_FLEECE;
  }

  public function prepareGoldenFleece($powerId)
  {
    $power = $this->getPower($powerId);
    $this->game->notifyAllPlayers('ramPowerSet', clienttranslate('Golden Fleece: Neighbor the Ram figure at the start of any turn to have the power of ${power_name}, ${power_title}'), [
      'i18n' => ['power_name', 'power_title'],
      'power_name' => $power->getName(),
      'power_title' => $power->getTitle(),
      'powerId' => $power->getId(),
    ]);
    $this->cards->moveCard($powerId, 'ramCard');
    self::DbQuery("INSERT INTO card (card_type, card_type_arg, card_location, card_location_arg) VALUES ('$powerId', 0, 'ram', 0), ('$powerId', 0, 'ram', 0)");
  }

  public function getGoldenFleecePowerId()
  {
    $ramCard = $this->cards->getCardsInLocation('ramCard');
    return empty($ramCard) ? null : array_values($ramCard)[0]['type'];
  }

  public function getGoldenFleecePower()
  {
    $powerId = $this->getGoldenFleecePowerId();
    return $this->game->powerManager->getPower($powerId);
  }

  public function checkGoldenFleece()
  {
    $ram = $this->game->board->getRam();
    $power = $this->getGoldenFleecePower();
    foreach ($this->game->playerManager->getPlayers() as $player) {
      $playerId = $player->getId();
      $workers = $this->game->board->getPlacedWorkers($playerId);
      Utils::filterWorkers($workers, function ($worker) use ($ram) {
        return $this->game->board->isNeighbour($worker, $ram, '');
      });

      $playerGoldenFleeceCards = array_values($this->cards->getCardsOfTypeInLocation($power->getId(), null, 'hand', $playerId));

      // Neighbouring ram => gain power
      if (count($workers) > 0 && count($playerGoldenFleeceCards) == 0) {
        $this->cards->pickCard('ram', $playerId);
        $this->notifyPower($player, $power, 'powerAdded', 'ram');
      } else if (count($workers) == 0 && count($playerGoldenFleeceCards) > 0) {
        $this->cards->moveCard($playerGoldenFleeceCards[0]['id'], 'ram');
        $this->notifyPower($player, $power, 'powerRemoved', 'ram');
      }
    }
  }

  public function addPower($power, $reason = null)
  {
    $player = $power->getPlayer();
    if ($player == null) {
      throw new BgaVisibleSystemException("addPower: Missing player (powerId: {$power->getId()})");
    }
    if ($reason == 'setup') {
      // Check the card for first player indicator
      $card = $this->cards->getCard($power->getId());
      if ($card['location_arg'] == 1) {
        $this->game->setGameStateValue('firstPlayer', $player->getId());
      }
      if (count($player->getPowers()) == 0) {
        // Record the power ID in game statistics
        $this->game->setStat($power->getId(), 'playerPower', $player->getId());
      }
    }
    if ($reason == 'setup' || $reason == 'hero') {
      // Draw the card
      $this->cards->moveCard($power->getId(), 'hand', $player->getId());
    }
    $this->notifyPower($player, $power, 'powerAdded', $reason);
    $player->addPlayerPower($power);
    if ($reason != 'hero') {
      // No setup when cancelling hero power discard
      $power->setup();
    }
  }

  public function removePower($power, $reason = null)
  {
    $player = $power->getPlayer();
    if ($player == null) {
      throw new BgaVisibleSystemException("removePower: Missing player (powerId: {$power->getId()})");
    }
    $moveTo = $reason == 'chaos' ? 'discard' : 'box';
    $this->cards->moveCard($power->getId(), $moveTo);
    $this->notifyPower($player, $power, 'powerRemoved', $reason);
    $player->removePlayerPower($power);
  }


  public function movePower($power, $newPlayer, $reason = null)
  {
    if ($newPlayer == null) {
      throw new BgaVisibleSystemException("movePower: Missing new player (powerId: {$power->getId()})");
    }
    $oldPlayer = $power->getPlayer();
    if ($oldPlayer == null) {
      throw new BgaVisibleSystemException("movePower: Missing old player (powerId: {$power->getId()})");
    }
    $this->cards->moveCard($power->getId(), 'hand', $newPlayer->getId());
    $this->notifyPower($newPlayer, $power, 'powerMoved', $reason);
    $oldPlayer->removePlayerPower($power);
    $newPlayer->addPlayerPower($power);
  }

  private function notifyPower($player, $power, $action = 'powerAdded', $reason = null)
  {
    $actionArgs['player_id'] = $player->getId();
    $actionArgs['power_id'] = $power->getId();
    $actionArgs['reason'] = $reason;
    $this->game->log->addAction($action, [], $actionArgs);

    $args = [
      'i18n' => ['power_name'],
      'player_id' => $player->getId(),
      'player_name' => $player->getName(),
      'power_id' => $power->getId(),
      'power_name' => $power->getName(),
      'reason' => $reason,
    ];
    $msg = '';
    if ($action == 'powerAdded' && $reason != 'hero') {
      // No notification message when cancelling hero power discard
      $msg = clienttranslate('${player_name} gains power ${power_name}');
    } else if ($action == 'powerRemoved') {
      $msg = clienttranslate('${player_name} discards power ${power_name}');
    } else if ($action == 'powerMoved') {
      $oldPlayer = $power->getPlayer();
      $args['player_id2'] = $oldPlayer->getId();
      $args['player_name2'] = $oldPlayer->getName();
      $msg = clienttranslate('${player_name} gains power ${power_name} from ${player_name2}');
    }
    $this->game->notifyAllPlayers($action, $msg, $args);
  }



  ///////////////////////////////////////
  ///////////////////////////////////////
  /////////    Apply power   ////////////
  ///////////////////////////////////////
  ///////////////////////////////////////
  public function applyPower($methods, $arg)
  {
    if (!is_array($methods)) {
      $methods = [$methods];
    }
    if (!is_array($arg)) {
      $arg = [$arg];
    }

    // First apply current user power(s)
    $playerId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($playerId);
    $method = array_shift($methods);
    foreach ($player->getPowers() as $power) {
      // Circe: Removed powers are still in this loop
      // For example, prevent startPlayerTurn() after Circe returns Morpheus
      if (!$this->hasPower($power->getId(), $playerId)) {
        continue;
      }
      call_user_func_array([$power, $method], $arg);
    }

    // Then apply teammate power(s) if needed
    if (count($methods) > 1) {
      $method = array_shift($methods);
      foreach ($this->game->playerManager->getTeammates($playerId, true) as $teammate) {
        foreach ($teammate->getPowers() as $power) {
          call_user_func_array([$power, $method], $arg);
        }
      }
    }

    // Then apply opponent power(s) if needed
    if (!empty($methods)) {
      $method = array_shift($methods);
      foreach ($this->game->playerManager->getOpponents($playerId) as $opponent) {
        foreach ($opponent->getPowers() as $power) {
          call_user_func_array([$power, $method], $arg);
        }
      }
    }
  }



  /*
   * argChooseFirstPlayer: is called either when the contestant has to choose first player
   *  or when the powers are assigned randomly
   */
  public function argChooseFirstPlayer(&$arg)
  {
    $powers = $arg['powers'];
    foreach ($powers as $powerId) {
      $this->getPower($powerId)->argChooseFirstPlayer($arg);
    }
  }


  /*
   * argPlaceWorker: is called when a player has to place a worker
   */
  public function argPlaceWorker(&$arg)
  {
    $this->argPlayerWork($arg, 'PlaceWorker');
  }


  ///////////////////////////////////
  ///////////////////////////////////
  /////////    Use Power   //////////
  ///////////////////////////////////
  ///////////////////////////////////

  /*
   * argUsePower: is called when a player may use their power
   */
  public function argUsePower(&$arg)
  {
    $this->applyPower(["argUsePower"], [&$arg]);
    Utils::cleanWorkers($arg);
  }

  /*
   * usePower: is called when a player want to use their power
   */
  public function usePower($powerId, $action)
  {
    $playerId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($playerId);
    foreach ($player->getPowers() as $power) {
      if ($power->getId() == $powerId) {
        $power->usePower($action);
      }
    }
  }


  /*
   * stateAfterSkipPower: is called whenever a player used their (non-standard) power
   */
  public function stateAfterSkipPower()
  {
    return $this->getNewState("stateAfterSkipPower");
  }

  /*
   * stateAfterUsePower: is called whenever a player used their (non-standard) power
   */
  public function stateAfterUsePower()
  {
    return $this->getNewState("stateAfterUsePower");
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
  private function argPlayerWork(&$arg, $action)
  {
    $this->applyPower(["argPlayer$action", "argTeammate$action", "argOpponent$action"], [&$arg]);
  }

  /*
   * argPlayerMove: is called whenever a player is going to do some move
   */
  public function argPlayerMove(&$arg)
  {
    $this->argPlayerWork($arg, 'Move');
    Utils::cleanWorkers($arg);
  }

  /*
   * argPlayerBuild: is called whenever a player is going to do some build
   */
  public function argPlayerBuild(&$arg)
  {
    $this->argPlayerWork($arg, 'Build');
    Utils::cleanWorkers($arg);
  }



  /////////////////////////////////////
  /////////////////////////////////////
  /////////    Work action   //////////
  /////////////////////////////////////
  /////////////////////////////////////

  /*
   * playerWork: is called whenever a player try to do some work (move/build).
   *    This is called after checking that the work is valid using argPlayerWork.
   *    This should return true if we want to bypass the usual work function:
   *      eg, Apollo can 'switch' instead of 'move'
   */
  public function playerWork($worker, $work, $action)
  {
    // First apply current user power(s)
    $name = "player" . $action;
    $playerId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($playerId);
    $r = array_map(function ($power) use ($worker, $work, $name) {
      return $power->$name($worker, $work);
    }, $player->getPowers());
    return count($r) > 0 ? max($r) : false;

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
  ////////   Afterwork hook   /////////
  /////////////////////////////////////
  /////////////////////////////////////

  /*
   * afterWork: is called after each work of each player.
   *  Useful for Harpies, Bia, ...
   */
  public function afterWork($worker, $work, $action)
  {
    $this->applyPower(["afterPlayer$action", "afterTeammate$action", "afterOpponent$action"], [$worker, $work]);
  }

  /*
   * afterMove: is called whenever a player just made a move
   */
  public function afterPlayerMove($worker, $work)
  {
    return $this->afterWork($worker, $work, 'Move');
  }

  /*
   * afterBuild: is called whenever a player just built
   */
  public function afterPlayerBuild($worker, $work)
  {
    return $this->afterWork($worker, $work, 'Build');
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
  public function getNewState($method)
  {
    $playerId = $this->game->getActivePlayerId();

    // Gaea: check stateAfterOpponentBuild first
    if ($method == 'stateAfterBuild') {
      foreach ($this->game->playerManager->getOpponents($playerId) as $opponent) {
        foreach ($opponent->getPowers() as $power) {
          $r = $power->stateAfterOpponentBuild();
          if ($r != null) {
            return $r;
          }
        }
      }
    }

    $player = $this->game->playerManager->getPlayer($playerId);
    $powers = $player->getPowers();
    $r = array_values(array_filter(array_map(function ($power) use ($method) {
      return $power->$method();
    }, $powers)));
    if (count($r) > 1) {
      $powerIds = implode(', ', Utils::getPowerIds($powers));
      throw new BgaVisibleSystemException("getNewState: Multiple values for $method (player: $playerId, power: $powerIds)");
    }

    if (count($r) == 1) {
      return $r[0];
    } else {
      return null;
    }
  }


  /*
   * stateAfterWork: is called whenever a player has done some work (in a regular way).
   *    This should return null if we want to continue as usual,
   *      or a valid transition name if we want something special.
   */
  public function stateAfterWork($action)
  {
    return $this->getNewState("stateAfter$action");
  }

  /*
   * stateAfterMove: is called after a regular move
   */
  public function stateAfterPlayerMove()
  {
    return $this->stateAfterWork('Move');
  }

  /*
   * stateAfterBuild: is called after a regular build
   */
  public function stateAfterPlayerBuild()
  {
    return $this->stateAfterWork('Build');
  }

  /////////////////////////////////////
  /////////////////////////////////////
  //////////  Start/end turn  /////////
  /////////////////////////////////////
  /////////////////////////////////////

  /*
   * TODO
   */
  public function startOfTurn()
  {
    if ($this->isGoldenFleece()) {
      $this->checkGoldenFleece();
    }
    $this->applyPower(["startPlayerTurn", "startTeammateTurn", "startOpponentTurn"], []);
  }

  /*
   * preEndOfTurn: called at the end of the turn, before the player confirms.
   *   player can preview and cancel/undo (e.g., discard hero power)
   */
  public function preEndOfTurn()
  {
    $this->applyPower(["preEndPlayerTurn", "preEndTeammateTurn", "preEndOpponentTurn"], []);
  }

  /*
   * endOfTurn: called at the end of the turn, after the player confirms.
   *   player cannot preview (e.g., Chaos draw new power)
   */
  public function endOfTurn()
  {
    $this->applyPower(["endPlayerTurn", "endTeammateTurn", "endOpponentTurn"], []);
  }


  /*
   * stateStartOfTurn: is called at the beginning of the player state.
   */
  public function stateStartOfTurn()
  {
    return $this->getNewState('stateStartOfTurn');
  }

  /*
   * stateAfterSkip: is called after a skip
   */
  public function stateAfterSkip()
  {
    return $this->getNewState('stateAfterSkip');
  }

  /*
   * stateStartOfTurn: is called at the end of the player turn, after the player confirms
   */
  public function stateEndOfTurn()
  {
    return $this->getNewState('stateEndOfTurn');
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
   *   Then we apply opponents powers that may do two things:
   *     - restrict a win : eg Aphrodite or Pegasus
   *     - steal a win : eg Moerae
   *     - make an opponent win : eg Chronus
   */
  public function checkWinning(&$arg)
  {
    // First apply current user power(s)
    $playerId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($playerId);
    foreach ($player->getPowers() as $power) {
      $power->checkPlayerWinning($arg);
    }

    // Then apply oponnents power(s)
    foreach ($this->game->playerManager->getOpponents($playerId) as $opponent) {
      foreach ($opponent->getPowers() as $power) {
        $power->checkOpponentWinning($arg);
      }
    }
  }
}
