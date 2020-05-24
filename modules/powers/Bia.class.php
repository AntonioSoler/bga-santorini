<?php

class Bia extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = BIA;
    $this->name  = clienttranslate('Bia');
    $this->title = clienttranslate('Goddess of Violence');
    $this->text  = [
      clienttranslate("Setup: Place your Workers first."),
      clienttranslate("Your Move: If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;

    $this->implemented = true;
  }

  /* * */

  public function argChooseFirstPlayer(&$arg)
  {
    $pId = $this->playerId;
    $arg['players'] = array_values(array_filter($arg['players'], function($player) use ($pId){
      return $player['id'] == $pId;
    }));

    $this->game->notifyAllPlayers('message', clienttranslate('${power_name}: ${player_name} must place its workers first'), [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
      'player_name' => $this->getPlayer()->getName(),
    ]);
  }


  public function afterPlayerMove($worker, $work)
  {
    $x = 2 * $work['x'] - $worker['x'];
    $y = 2 * $work['y'] - $worker['y'];

    // If there is no opponent in the next space -> return null
    $worker2 = self::getObjectFromDB("SELECT * FROM piece WHERE x = {$x} AND y = {$y} AND type = 'worker'");
    if ($worker2 == null || $worker2['player_id'] == $worker['player_id']) {
      return;
    }

    $this->game->playerKill($worker2, $this->getName());
  }
}
