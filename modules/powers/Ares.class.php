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
  }

  /* * */
  /*
  public function argUsePower(&$arg)
   {

     $accessibleSpaces = $this->game->board->getAccessibleSpaces('move');

     // Otherwise, let the player do a second move (not mandatory) with same worker
     $arg['skippable'] = true;
     $arg['selectable'] = []; // TODO c'est ca que tu veux?    liste des spaces sur qui le pouvoir peut s'activer

     $myWorkers = Utils::getPlacedActiveWorkers();

     foreach ($myWorkers as $worker) {

       $spaces = $this->game->board->getNeighbouringSpaces($worker, '');

       // TODO j'ai pas trouvé plus dégeulasse pour merger les deux array
       Utils::filter($spaces, function ($space) use (&$arg) {
         $test =  array_values(array_filter($arg['selectable'], function ($prevSpace) use ($space){
           return $this->game->board->isSameSpace($prevSpace, $space);
         }));
         if (count($test) > 0)
           return false;
         return $space['z'] > 0;
       } );

       $arg['selectable'] = array_merge($arg['selectable'], $spaces);

     }

       return count(arg['selectable']) > 0;
   }


   public function UsePower($space)
   {

     // Remove piece
     $piece = // TODO: comment tu recupere l'id ?
     $this->game->log->addRemoval($piece, $space);
     self::DbQuery("DELETE FROM piece WHERE x = {$space['x']}, y = {$space['y']}, z = {$space['z']}}");

     // Notify
     $this->game->notifyAllPlayers('pieceRemoved', clienttranslate('${power_name}: ${player_name} removes a block'), [ // TODO: UI notif
       'i18n' => ['power_name'],
       'piece1' => $piece,
       'power_name' => $this->getName(),
       'player_name' => $this->game->getActivePlayerName(),
     ]);

     return true;
   }


   public function stateAfterBuild()
   {
     return 'usePower';
   }
   */
 }
