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
      clienttranslate("Setup: Shuffle the Fortune card and five blank cards (or one Advanced God card and five Simple God cards) into a face-down deck in your play area.
End of Your Turn: Turn over the top card of your deck. If it is a blank card, place it under your deck. If it is the Fortune card, reshuffle it into your deck and you may take an additional turn. On that additional turn, do not
turn over the top card from the deck")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 48;
  }

  /* * */
}
