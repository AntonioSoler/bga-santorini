<?php

class Tyche extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = TYCHE;
    $this->name  = clienttranslate('Tyche');
    $this->title = clienttranslate('Goddess of Fortune');
    $this->text  = [
      clienttranslate("[Setup:] Shuffle a deck of 6 cards in your play area."),
      clienttranslate("[End of Your Turn:] Draw the top card of your deck. If it is 1 - 5, discard it. If it is 6, reshuffle your deck and you may take an additional turn. On that additional turn, do not draw the top card from your deck."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 48;

    $this->implemented = true;
  }

  /* * */

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['counter'] = ($this->playerId != null) ? $this->computeDeck() : 6;
    return $data;
  }

  public function computeDeck()
  {
    return intval($this->game->powerManager->cards->countCardInLocation('tycheDeck'));
  }

  public function drawCard()
  {
    $card = $this->game->powerManager->cards->pickCardForLocation('tycheDeck', 'tycheDiscard');
    $additionalTurn = ($card['type'] == 1);
    if ($additionalTurn) {
      $this->game->powerManager->cards->moveAllCardsInLocation('tycheDiscard', 'tycheDeck');
      $this->game->powerManager->cards->shuffle('tycheDeck');
      $this->game->additionalTurn($this);
    } else {
      $this->game->notifyAllPlayers('message', $this->game->msg['powerNoAdditionalTurn'], [
        'i18n' => ['power_name'],
        'power_name' => $this->getName(),
        'player_name' => $this->getPlayer()->getName(),
      ]);
    }
    $this->updateUI();
    return $additionalTurn;
  }

  public function setup()
  {
    // Create the deck of 6 cards
    $cards = [
      ['type' => 0, 'type_arg' => 0, 'nbr' => 5], // nothing
      ['type' => 1, 'type_arg' => 1, 'nbr' => 1], // additional turn
    ];
    $this->game->powerManager->cards->createCards($cards, 'tycheDeck');
    $this->game->powerManager->cards->shuffle('tycheDeck');
  }

  public function stateEndOfTurn()
  {
    return (!$this->game->log->isAdditionalTurn(TYCHE) && $this->drawCard()) ? 'additionalTurn' : null;
  }

  public function argPlayerMove(&$arg)
  {
    // Usual turn => usual rule
    if (!$this->game->log->isAdditionalTurn(TYCHE)) {
      return;
    }
    $arg['skippable'] = true;
  }

  public function stateAfterSkip()
  {
    return 'endturn';
  }
}
