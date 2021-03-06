<?php

class Atlas extends SantoriniPower
{
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = ATLAS;
    $this->name  = clienttranslate('Atlas');
    $this->title = clienttranslate('Titan Shouldering the Heavens');
    $this->text  = [
      clienttranslate("[Your Build:] Your Worker may build a dome at any level."),
    ];
    $this->playerCount = [2, 3, 4];
    $this->golden  = true;
    $this->orderAid = 40;

    $this->implemented = true;
  }

  /* * */

  public function argPlayerBuild(&$arg)
  {
    foreach ($arg["workers"] as &$worker) {
      foreach ($worker["works"] as &$work) {
        if (!in_array(3, $work['arg'])) {
          $work['arg'][] = 3;
        }
      }
    }
  }

  public function afterPlayerBuild($worker, $work)
  {
    if ($work['arg'] == 3 && $work['z'] != 3) {
      $stats = [[$this->playerId, 'usePower']];
      $this->game->log->addAction('stats', $stats);
    }
  }
}
