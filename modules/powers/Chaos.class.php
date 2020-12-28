<?php

class Chaos extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = CHAOS;
    $this->name  = clienttranslate('Chaos');
    $this->title = clienttranslate('Primordial Nothingness');
    $this->text  = [
      clienttranslate("[Setup:] Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck."),
      clienttranslate("[Any Time:] You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false; // TODO the rules allow this
    // TODO: Need to verify using Chaos as Nyx's Night Power
    $this->orderAid = 34;

    $this->implemented = true;
  }

  /* * */

  public function getUiData($playerId)
  {
    $data = parent::getUiData($playerId);
    $data['counter'] = ($this->playerId != null) ? $this->computeDeck() : 0;
    return $data;
  }

  public function computeDeck()
  {
    return intval($this->game->powerManager->cards->countCardInLocation('deck'));
  }

  public function pickNewPower()
  {
    $card = $this->game->powerManager->cards->pickCard('deck', $this->playerId);
    $power = $this->game->powerManager->getPower($card['id'], $this->playerId);
    $this->game->powerManager->addPower($power, 'chaos');
    $this->updateUI();
  }

  public function setup()
  {
    // Recreate the deck with just non-banned Simple Gods
    $powerIds = $this->game->powerManager->getPowerIdsInLocation('hand');
    $nyxNightId = $this->game->powerManager->getSpecialPowerId('nyxNight');
    if ($nyxNightId != null) {
      $powerIds[] = $nyxNightId;
    }
    $banned = $this->game->powerManager->computeBannedIds($powerIds);
    $this->game->powerManager->cards->moveAllCardsInLocation('deck', 'box');
    for ($i = 1; $i <= 10; $i++) {
      if (in_array($i, $banned)) {
        continue;
      }
      $card = $this->game->powerManager->cards->getCard($i);
      if ($card['location'] == 'box') {
        $this->game->powerManager->cards->moveCard($i, 'deck');
      }
    }
    $this->game->powerManager->cards->shuffle('deck');

    // Then pick a card
    $this->pickNewPower();
  }

  public function endPlayerTurn()
  {
    $works = $this->game->log->getLastBuilds();
    $dome = false;
    for ($i = 0; !$dome && $i < count($works); $i++) {
      $dome = $works[$i]['to']['arg'] == 3;
    }
    if (!$dome) {
      return;
    }

    $this->game->notifyAllPlayers('message', $this->game->msg['powerDomeBuilt'], [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
    ]);

    // Discard current Simple God power and add new
    foreach ($this->getPlayer()->getPowers() as $power) {
      if ($power->isSimple()) {
        $this->game->powerManager->removePower($power, 'chaos');
      }
    }
    $this->pickNewPower();
  }

  public function endOpponentTurn()
  {
    $this->endPlayerTurn();
  }

  public function endTeammateTurn()
  {
    $this->endPlayerTurn();
  }
}
