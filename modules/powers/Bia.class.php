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
      clienttranslate("[Setup:] Place your Workers first. Your workers must be placed in perimeter spaces."),
      clienttranslate("[Your Move:] If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game."),
      clienttranslate("[REVISED POWER]"),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 0;

    $this->implemented = true;
  }

  /* * */

  public function argChooseFirstPlayer(&$arg)
  {
    $pId = $this->getId();
    Utils::filter($arg['powers'], function ($power) use ($pId) {
      return $power == $pId;
    });

    $this->game->notifyAllPlayers('message', $this->game->msg['firstPlayer'], [
      'i18n' => ['power_name'],
      'power_name' => $this->getName(),
    ]);
  }

  public function argPlayerPlaceWorker(&$arg)
  {
    Utils::filter($arg['accessibleSpaces'], function ($space) {
      return $this->game->board->isPerimeter($space);
    });
  }


  public function afterPlayerMove($worker, $work)
  {
    $x = 2 * $work['x'] - $worker['x'];
    $y = 2 * $work['y'] - $worker['y'];

    // Must use getPlacedOpponentWorkers() so Bia cannot target Clio's invisible workers
    $oppWorkers = $this->game->board->getPlacedOpponentWorkers();
    foreach ($oppWorkers as &$oppWorker) {
      if ($oppWorker['x'] == $x and $oppWorker['y'] == $y) {
        $this->game->playerKill($oppWorker, $this->getName());
        break;
      }
    }
  }
}
