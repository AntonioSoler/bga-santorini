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
      clienttranslate("[End of Your Turn:] You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = false;
    $this->orderAid = 62;

    $this->implemented = true;
  }

  /* * */
  public function stateAfterBuild()
  {
    $arg = [];
    $this->argUsePower($arg);
    Utils::cleanWorkers($arg);
    return (count($arg['workers']) > 0) ? 'power' : null;
  }


  public function argUsePower(&$arg)
  {
    $arg = $this->game->argPlayerWork('build');
    $arg['power'] = $this->id;
    $arg['power_name'] = $this->name;
    $arg['skippable'] = true;

    $move = $this->game->log->getLastMove();
    Utils::filterWorkersById($arg, $move['pieceId'], false);
    Utils::filterWorks($arg, function ($space, $worker) {
      return $space['z'] > 0;
    });
  }


  public function usePower($action)
  {
    // Extract info from action
    $wId = $action[0];
    $space = $action[1];
    $space['z']--;

    // Remove the piece(s) at this space x,y,z
    $pieces = $this->game->board->getPiecesAt($space);
    foreach ($pieces as $piece) {
      $this->removePiece($piece);
    }
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
