<?php

class Persephone extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = PERSEPHONE;
    $this->name  = clienttranslate('Persephone');
    $this->title = clienttranslate('Goddess of Spring Growth');
    $this->text  = [
      clienttranslate("[Opponent's Turn:] If possible, at least one Worker must move up this turn."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 4;

    $this->implemented = true;
  }

  /*
DISCLAIMER :
This is a very basic version of Persephone that will not work against power that may move more than once (or that may free some space using their power)
That is why I added some banned matchups that are not in the rulebook.
*/

  /* * */
  public function argOpponentMove(&$arg)
  {
    $canMoveUp = false;
    foreach ($arg["workers"] as &$worker) {
      foreach ($worker['works'] as &$space) {
        if ($space['z'] > $worker['z']) {
          $canMoveUp = true;
        }
      }
    }

    if ($canMoveUp) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
      Utils::filterWorks($arg, function ($space, $worker) {
        return $space['z'] > $worker['z'];
      });
    }
  }
}
