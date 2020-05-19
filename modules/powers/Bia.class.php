<?php

class Bia extends SantoriniPower
{
  public function isImplemented(){ return true; }

  public static function getId() {
    return BIA;
  }

  public static function getName() {
    return clienttranslate('Bia');
  }

  public static function getTitle() {
    return clienttranslate('Goddess of Violence');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Place your Workers first."),
      clienttranslate("Your Move: If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [NEMESIS, TARTARUS];
  }

  public static function isGoldenFleece() {
    return true;
  }

  /* * */

  // TODO setup: 1st player


  public function afterPlayerMove($worker, $work)
  {
    $x = 2*$work['x'] - $worker['x'];
    $y = 2*$work['y'] - $worker['y'];

    // If there is no opponent in the next space -> return null
    $worker2 = self::getObjectFromDB( "SELECT * FROM piece WHERE x = {$x} AND y = {$y} AND type = 'worker'");
    if ($worker2 == null || $worker2['player_id'] == $worker['player_id'])
      return null;

    // Kill worker
    self::DbQuery( "UPDATE piece SET location = 'box' WHERE id = {$worker2['id']}" );
    $this->game->log->addRemoval($worker2);

    // Notify
    $args = [
      'i18n' => [],
      'piece' => $worker2,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
      'player_name2' => $this->game->playerManager->getPlayer($worker2['player_id'])->getName(),
    ];
    $this->game->notifyAllPlayers('pieceRemoved', clienttranslate('${power_name}: ${player_name} kills a worker of ${player_name2}'), $args);
    return null;
  }


}
