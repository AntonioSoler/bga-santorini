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
      clienttranslate("Opponent's Turn: If possible, at least one Worker must move up this turn.")
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 4;

    $this->implemented = true;
  }

/*
DISCLAIMER :
This is a very basic version of Persephone that will not work against power that may move more than once.
That is why I added some banned matchups that are not in the rulebook.
If one wants to improve this, it will also have to change Triton behaviour : already visited spaces cannot be moved on again
  (useful against Aphrodite to make sure we can be blocked and then resign at some point)
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

    if($canMoveUp){
      Utils::filterWorks($arg, function($space, $worker){
        return $space['z'] > $worker['z'];
      });
    }
  }
}
