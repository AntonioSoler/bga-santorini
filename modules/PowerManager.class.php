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
    [APHRODITE, SCYLLA], // Scylla should be allowed to go away and drag Aphrodite
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
    [HARPIES, TARTARUS], // until "forces after win" are deleted
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
    [NYX, CHAOS], // https://boardgamearena.com/bug?id=29287
    [NYX, CHRONUS], // https://boardgamearena.com/bug?id=33571
    [NYX, DIONYSUS], // https://boardgamearena.com/bug?id=24644
    [SELENE, GAEA],
    [TARTARUS, TERPSICHORE],
    [CHARYBDIS, HARPIES], // Charybdis should go first which does not work with 3 (and 4?) players. Fine with 2 though but cannot ban only 2

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
    [ADONIS, NEMESIS], // move workers at the end
    [ADONIS, PROMETHEUS],  // movement restriction
    [ADONIS, PROTEUS], // can teleport a worker
    [ADONIS, SIREN], // alternative turn
    [ADONIS, TERPSICHORE], // both workers must move
    [ADONIS, TRITON], // multiple moves

    // Incomplete Hecate implementation: ban powers targetting opponent workers and other features to add
    [HECATE, CHAOS], // Chaos should switch powers if a dome is built before an illegal action but not if building the dome was illegal
    [HECATE, CHARYBDIS], // not compatible with restarts if Hecate blocks the other whirlpool + during Hecate turn
    [HECATE, ERIS],
    [HECATE, IRIS], // can jump over secret workers but not build on the step / Hecate has to detect if this was legal + niche issue with restart implementation: may jump to a place where she cannot build, which is a possible move if Hecate is here (equivalent to pass the turn)
    [HECATE, NEMESIS],
    [HECATE, NYX], // too confusing for the moment
    [HECATE, SIREN],
    [HECATE, HYDRA], // should propose a higher level to spawn when <3 are of minimal height

    // Hecate ban all heroes https://boardgamearena.com/bug?id=37708
    [HECATE, ACHILLES],
    [HECATE, ADONIS],
    [HECATE, ATALANTA],
    [HECATE, BELLEROPHON],
    [HECATE, HERACLES],
    [HECATE, JASON],
    [HECATE, MEDEA],
    [HECATE, ODYSSEUS],
    [HECATE, POLYPHEMUS],
    [HECATE, THESEUS],
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
  public function getUiData($playerId)
  {
    $ui = [];
    foreach ($this->getPowers() as $power) {
      if ($power->isImplemented()) {
        $data = $power->getUiData($playerId);
        $data['banned'] = $this->computeBannedIds([$power->getId()]);
        if (array_key_exists('counter', $data) && is_array($data['counter'])) {
          // When counter is an array, show different info to each player
          // Select the correct value for the current player
          $data['counter'] = array_key_exists($playerId, $data['counter']) ? $data['counter'][$playerId] : $data['counter']['all'];
        }
        $ui[$power->getId()] = $data;
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
    $sql = 'INSERT INTO card (card_id, card_type, card_type_arg, card_location, card_location_arg) VALUES ';
    $values = [];
    foreach (array_keys(self::$classes) as $powerId) {
      $values[] = "($powerId, '$powerId', 0, 'box', 0)";
    }
    self::DbQuery($sql . implode(',', $values));
  }

  /*
   * preparePowers: move supported power cards to the deck and transition to the next state
   */
  public function preparePowers()
  {
    $nPlayers = $this->game->playerManager->getPlayerCount();
    $optionGoldenFleece = intval($this->game->getGameStateValue('optionGoldenFleece'));
    $optionSimple = intval($this->game->getGameStateValue('optionSimple'));
    $optionHero = intval($this->game->getGameStateValue('optionHero'));
    $optionAdvanced = intval($this->game->getGameStateValue('optionAdvanced'));
    $optionSetup = intval($this->game->getGameStateValue('optionSetup'));

    if ($nPlayers > 2 && $optionSimple == NO && $optionAdvanced == NO) {
      // Can't play without powers for 3 or 4 players
      // Pretend they picked Simple Gods + Quick Setup
      $optionSimple = YES;
      $optionSetup = QUICK;
    }

    $powerIds = [];
    foreach ($this->getPowers() as $power) {
      if ($power->isImplemented() && in_array($nPlayers, $power->getPlayerCount())) {
        if ($optionGoldenFleece == YES) {
          $match = $power->isGoldenFleece();
        } else {
          $match = ($optionSimple == YES && $power->isSimple())
            || ($optionHero == YES && $power->isHero())
            || ($optionAdvanced == YES && $power->isAdvanced())
            || ($optionAdvanced == PERFECT && $power->isPerfectInformation() && $power->isAdvanced());
        }
        if ($match) {
          $powerIds[]  = $power->getId();
        }
      }
    }

    if (!empty($powerIds) && ($optionSetup == QUICK || $optionSetup == LIMITED)) {
      $count = $optionSetup == QUICK ? ($optionGoldenFleece == YES ? 1 : $nPlayers) : ($nPlayers + 1) * 2;
      $offer = [];
      for ($i = 0; $i < $count; $i++) {
        $offer[] = $powerIds[array_rand($powerIds, 1)];
        Utils::filter($powerIds, function ($id) use ($offer) {
          // Remove the selected powers AND any banned powers
          return !in_array($id, $offer) && !in_array($id, $this->computeBannedIds($offer));
        });
      }
      if (count($offer) != $count) {
        throw new Exception("preparePowers: Wrong number of limited powers (expected: $count, actual: " . count($offer) . ")");
      }
      $powerIds = $offer;
    }

    if (empty($powerIds)) {
      // No powers
      $this->game->gamestate->nextState('placeWorker');
    } else if ($optionSetup == QUICK && $optionGoldenFleece == YES) {
      // Golden Fleece already chosen
      $this->setSpecialPower('ram', $powerIds[0], true);
      $this->game->gamestate->nextState('placeWorker');
    } else if ($optionSetup == QUICK) {
      // Auto-confirm the offer
      $this->cards->moveCards($powerIds, 'offer');
      $this->game->confirmOffer(true);
    } else {
      // Build offer
      $this->cards->moveCards($powerIds, 'deck');
      $this->cards->shuffle('deck');
      $this->game->gamestate->nextState('offer');
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
          $ids[$matchup[1]] = true;
        }
        if ($matchup[1] == $power) {
          $ids[$matchup[0]] = true;
        }
      }
    }
    return array_keys($ids);
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
    $this->game->notifyAllPlayers('message', $this->game->msg['firstPlayer'], [
      'i18n' => ['power_name'],
      'power_name' => $this->getPower($powerId)->getName(),
    ]);
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

  public function getSpecialPowerId($location)
  {
    $powerId = $this->getPowerIdsInLocation($location);
    return empty($powerId) ? null : $powerId[0];
  }

  public function getSpecialPower($location, $playerId = null)
  {
    $powerId = $this->getSpecialPowerId($location);
    return $powerId == null ? null : $this->getPower($powerId, $playerId);
  }

  public function setSpecialPower($location, $powerId, $autoConfirm = false)
  {
    $this->cards->moveCard($powerId, $location);
    $power = $this->getPower($powerId);
    $this->game->notifyAllPlayers('specialPowerSet', $this->game->msg['specialPower'], [
      'i18n' => ['power_name', 'special_name'],
      'player_name' => $autoConfirm ? 'Board Game Arena' : $this->game->getActivePlayerName(),
      'power_name' => $power->getName(),
      'powerId' => $powerId,
      'location' => $location,
      'special_name' => $this->game->specialNames[$location],
    ]);

    if ($location == 'ram') {
      // Extra reminder about Golden Fleece
      $this->game->notifyAllPlayers('message', clienttranslate('Golden Fleece: Neighbor the Ram figure at the start of any turn to have the power of ${power_name}, ${power_title}'), [
        'i18n' => ['power_name', 'power_title'],
        'power_name' => $power->getName(),
        'power_title' => $power->getTitle(),
      ]);
    } else if ($location == 'nyxNight') {
      // Return all 'nyxDeck' back to 'deck', needed for Chaos
      $this->cards->moveAllCardsInLocation('nyxDeck', 'deck');
    }
  }

  public function isGoldenFleece()
  {
    return intval($this->game->getGameStateValue('optionGoldenFleece')) == YES;
  }

  public function checkGoldenFleece()
  {
    $ram = $this->game->board->getRam();
    foreach ($this->game->playerManager->getPlayers() as $player) {
      $playerId = $player->getId();
      $power = $this->getSpecialPower('ram', $playerId);
      $workers = $this->game->board->getPlacedWorkers($playerId);
      Utils::filterWorkers($workers, function ($worker) use ($ram) {
        return $this->game->board->isNeighbour($worker, $ram, '');
      });

      $hasPower = $this->hasPower($power->getId(), $playerId);
      if (!$hasPower && count($workers) > 0) {
        $this->addPower($power, 'ram');
      } else if ($hasPower && count($workers) == 0) {
        $this->removePower($power, 'ram');
      }
    }
  }

  ///////////////////////////////////////
  ///////////////////////////////////////
  /////////  Nyx's Night Power  /////////
  ///////////////////////////////////////
  ///////////////////////////////////////

  /*
   * prepareNyxNightPowers: move night powers to 'nyxDeck' and transition to the next state
   */
  public function prepareNyxNightPowers()
  {
    $offer = $this->getPowerIdsInLocation('offer');
    $powers = array_filter($this->getPowers(), function ($power) use ($offer) {
      return !in_array($power->getId(), $offer) && !in_array($power->getId(), $this->computeBannedIds($offer)) && $power->isImplemented() && $power->isGoldenFleece();
    });
    $powerIds = array_values(array_map(function ($power) {
      return $power->getId();
    }, $powers));

    $optionSetup = intval($this->game->getGameStateValue('optionSetup'));
    if ($optionSetup == QUICK || $optionSetup == LIMITED) {
      $count = $optionSetup == QUICK ? 1 : 6;
      if (count($powerIds) < $count) {
        throw new BgaVisibleSystemException("prepareNyxNightPowers: Not enough powers available (expected: $count, actual: " . count($powerIds) . ")");
      }
      shuffle($powerIds);
      array_splice($powerIds, $count);
    }

    if ($optionSetup == QUICK) {
      // Auto-confirm Nyx's Night Power
      $this->setSpecialPower('nyxNight', $powerIds[0], true);
      $this->game->gamestate->nextState('chooseFirstPlayer');
    } else {
      // Build offer
      $this->cards->moveCards($powerIds, 'nyxDeck');
      $this->game->gamestate->nextState('nyx');
    }
  }

  public function addPower($power, $reason = null)
  {
    $player = $power->getPlayer();
    if ($player == null) {
      throw new BgaVisibleSystemException("addPower: Missing player (powerId: {$power->getId()}, reason: $reason)");
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
    if ($reason == 'setup' || $reason == 'hero' || $reason == 'nyx') {
      // Draw the card
      $this->cards->moveCard($power->getId(), 'hand', $player->getId());
    } else if ($reason == 'ram' || $reason == 'nyxNight') {
      // Duplicate this card into the player's hand
      self::DbQuery("INSERT INTO card (card_type, card_type_arg, card_location, card_location_arg) VALUES ('{$power->getId()}', 0, 'hand', {$player->getId()})");
    }
    $this->notifyPower($player, $power, 'powerAdded', $reason);
    $player->addPlayerPower($power);
    if ($reason != 'hero' && $reason != 'ram' && $reason != 'nyxNight' && $reason != 'nyx') {
      // No setup when cancelling hero power discard
      // No setup for Golden Fleece or Nyx
      $power->setup();
    }
  }

  public function removePower($power, $reason = null)
  {
    $player = $power->getPlayer();
    if ($player == null) {
      throw new BgaVisibleSystemException("removePower: Missing player (powerId: {$power->getId()}, reason: $reason)");
    }
    if ($reason == 'ram' || $reason == 'nyxNight') {
      // Destroy the duplicate card
      self::DbQuery("DELETE FROM card WHERE card_type = '{$power->getId()}' AND card_location = 'hand' AND card_location_arg = {$player->getId()}");
    } else {
      // Move the card to the discard location
      $moveTo = 'box';
      if ($reason == 'chaos') {
        $moveTo = 'discard';
      } else if ($reason == 'nyx') {
        $moveTo = 'nyx';
      }
      $this->cards->moveCard($power->getId(), $moveTo);
    }
    $this->notifyPower($player, $power, 'powerRemoved', $reason);
    $player->removePlayerPower($power);
  }


  public function movePower($power, $newPlayer, $reason = null)
  {
    if ($newPlayer == null) {
      throw new BgaVisibleSystemException("movePower: Missing new player (powerId: {$power->getId()}, reason: $reason)");
    }
    $oldPlayer = $power->getPlayer();
    if ($oldPlayer == null) {
      throw new BgaVisibleSystemException("movePower: Missing old player (powerId: {$power->getId()}, reason: $reason)");
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
      $msg = $this->game->msg['powerGain'];
    } else if ($action == 'powerRemoved') {
      $msg = $this->game->msg['powerDiscard'];
    } else if ($action == 'powerMoved') {
      $oldPlayer = $power->getPlayer();
      $args['player_id2'] = $oldPlayer->getId();
      $args['player_name2'] = $oldPlayer->getName();
      $msg = $this->game->msg['powerGainFrom'];
    }
    if ($reason == 'nyx' || ($reason == 'chaos' && $action == 'powerRemoved')) {
      $args['duration'] = INSTANT;
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
   * argPlaceSetup: is called when a player may perform a setup
   */
  public function argPlaceSetup(&$arg)
  {
    $this->applyPower(["argPlaceSetup"], [&$arg]);
    Utils::cleanWorkers($arg);
  }

  /*
   *placeSetup: is called when a player wants to setup
   */
  public function placeSetup($powerId, $action)
  {
    $playerId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($playerId);
    foreach ($player->getPowers() as $power) {
      if ($power->getId() == $powerId) {
        $power->placeSetup($action);
      }
    }
  }

  public function stateAfterPlaceSetup()
  {
    return $this->getNewState("stateAfterPlaceSetup");
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
