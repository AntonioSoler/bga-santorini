<?php

class Ares extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ARES;
    $this->name  = clienttranslate('Ares');
    $this->title = clienttranslate('God of War');
    $this->text  = [
      clienttranslate("End of Your Turn: You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;

    $this->implemented = true;
  }

  /* * */
  public function stateAfterBuild()
  {
    $arg = [];
    $this->argUsePower($arg);
    return (count($arg['workers']) > 0)? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $arg['workers'] = $this->game->board->getPlacedActiveWorkers();
    $move = $this->game->log->getLastMove();
    Utils::filterWorkersById($arg, $move['pieceId'], false);

    foreach ($arg['workers'] as &$worker) {
      $worker['works'] = $this->game->board->getNeighbouringSpaces($worker, '');
      Utils::filter($worker['works'], function ($space){
        return $space['z'] > 0;
      });
    }

    Utils::cleanWorkers($arg);
  }


  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $space['z']--;

    // Remove piece
    $piece = $this->game->board->getPieceAt($space);
    self::DbQuery("UPDATE piece SET location = 'box' WHERE id = {$piece['id']}");
    $this->game->log->addRemoval($piece);

    // Notify
    $this->game->notifyAllPlayers('pieceRemoved', clienttranslate('${power_name}: ${player_name} removes a block'), [
      'i18n' => ['power_name'],
      'piece' => $piece,
      'power_name' => $this->getName(),
      'player_name' => $this->game->getActivePlayerName(),
    ]);
   }

   public function stateAfterUsePower()
   {
     return 'endturn';
   }

   public function stateAfterSkipPower()
   {
     return 'endturn';
   }
}
