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
      clienttranslate("[Any Time:] You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false; // TODO the rules allow this
    $this->orderAid = 34;

    $this->implemented = true;
  }

  /* * */
  public function getUIData()
  {
    $data = parent::getUIData();
    $data['counter'] = $this->computeDeck();
    return $data;
  }

  public function computeDeck()
  {
    return intval($this->game->powerManager->cards->countCardInLocation('deck'));
  }

  public function updateUI()
  {
    $this->game->notifyAllPlayers('updatePowerUI', '', [
      'playerId' => $this->playerId,
      'powerId' => $this->getId(),
      'counter' => $this->computeDeck()
    ]);
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
    $banned = $this->game->powerManager->computeBannedIds('hand');
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

  public function endOfTurn()
  {
    $works = $this->game->log->getLastBuilds();
    $dome = false;
    for ($i = 0; !$dome && $i < count($works); $i++) {
      $dome = $works[$i]['to']['arg'] == 3;
    }

    if (!$dome) {
      return;
    }

    // Discard current Simple God power
    foreach ($this->getPlayer()->getPowers() as $power) {
      if ($power->isSimple()) {
        $this->game->powerManager->removePower($power, 'chaos');
      }
    }

    // Add new
    $this->pickNewPower();
  }

  public function endPlayerTurn()
  {
    $this->endOfTurn();
  }

  public function endOpponentTurn()
  {
    $this->endOfTurn();
  }
}
