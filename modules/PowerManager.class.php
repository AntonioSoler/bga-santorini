<?php

// TODO : description
class PowerManager extends APP_GameClass
{
  public $game;

  public function __construct($game)
  {
    $this->game = $game;
  }

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

  public function argPlayerMove(&$arg)
  {
    // First apply current user power(s)
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    foreach($player->getPowers() as $power)
      $power->argPlayerMove($arg);

    // Then apply oponnents power(s)
    foreach($this->game->playerManager->getOpponents($pId) as $opponent)
    foreach($opponent->getPowers() as $power)
      $power->argOpponentMove($arg);
  }


  public function playerMove($wId, $x, $y, $z)
  {
    // First apply current user power(s)
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    $r = array_map(function($power) use ($wId, $x, $y, $z){
      return $power->playerMove($wId, $x, $y, $z);
    }, $player->getPowers());
    return max($r);

    // TODO use an opponentMove function ?
  }


  public function stateAfterMove()
  {
    $pId = $this->game->getActivePlayerId();
    $player = $this->game->playerManager->getPlayer($pId);
    $r = array_filter(array_map(function($power){
      return $power->stateAfterMove();
    }, $player->getPowers()));
    if(count($r) > 1)
      throw new BgaUserException(_("Can't figure next state after move"));

    if(count($r) == 1)
      return $r[0];
    else
      return null;
  }


}
