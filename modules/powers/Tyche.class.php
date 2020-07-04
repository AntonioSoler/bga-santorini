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
      clienttranslate("[Setup:] Shuffle five Advanced God cards and one Simple God card into a face-down deck in your play area."),
      clienttranslate("[End of Your Turn:] Turn over the top card of your deck. If it is an Advanced God card, place it under your deck. If it is the Simple God card, reshuffle it into your deck and you may take an additional turn. On that additional turn, do not turn over the top card from the deck."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 48;
  }

  /* * */
}
