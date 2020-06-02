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
      clienttranslate("Setup: Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck."),
      clienttranslate("Any Time: You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 34;

    $this->implemented = true;
  }

  /* * */
  public function pickNewPower()
  {
    $card = $this->game->powerManager->cards->pickCard('deck', $this->playerId);
    $power = $this->game->powerManager->getPower($card['id']);
    $this->game->notifyAllPlayers('powersChanged', clienttranslate('${power_name}: ${player_name} has a new power : ${power_name2}'), [
      'i18n' => ['power_name', 'power_name2'],
      'power_name' => $this->getName(),
      'power_name2' => $power->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'fplayers' => $this->game->playerManager->getUiData(),
    ]);
  }

  public function setup()
  {
    // Remove all non simple gods
    $cards = $this->game->powerManager->cards->getCardsInLocation('deck');
    foreach($cards as $card){
      if($card['id'] > 10){
        $this->game->powerManager->cards->moveCard($card['id'], 'box');
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
    for($i = 0; !$dome && $i < count($works); $i++){
      $dome = $works[$i]['to']['arg'] == 3;
    }

    if(!$dome){
      return;
    }

    // Discard current
    foreach($this->game->playerManager->getPlayer($this->playerId)->getPowers() as $power){
      if($power->getId() != $this->getId()){
        $this->game->powerManager->cards->moveCard($power->getId(), 'discard');
      }
    }

    // Add new
    $this->pickNewPower();
  }

  public function endPlayerTurn(){
    $this->endOfTurn();
  }

  public function endOpponentTurn(){
    $this->endOfTurn();
  }

}
