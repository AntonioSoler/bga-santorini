<?php

abstract class Power extends APP_GameClass {

    protected $game;
    protected $playerId;

    public function __construct($game, $playerId) {
      $this->game = $game;
      $this->playerId = $playerId;
    }

    public function setup($player) {}

    public function stateStartTurn() { return null; }
    public function stateAfterSkip() { return null; }

    public function beforeMove() {}
    public function argPlayerMove(&$arg) { }
    public function argOpponentMove(&$arg) { }
    public function playerMove($worker, $work) { return false; }
    public function stateAfterMove(){ return null; }

    public function beforeBuild() {}
    public function argPlayerBuild(&$arg) { }
    public function argOpponentBuild(&$arg) { }
    public function playerBuild($worker, $work) { return false; }
    public function stateAfterBuild(){ return null; }
    public function build() {}

    public function endTurn() {}
    public function checkPlayerWinning(&$arg) {}
    public function checkOpponentWinning(&$arg) {}

    /* TODO */
    protected function filterWorkers(&$arg, $filter){
      $arg['workers'] = array_values(array_filter($arg['workers'], $filter));
    }


    protected function filterWorkersById(&$arg, $wId){
      $this->filterWorkers($arg, function($worker) use ($wId){
        return $worker['id'] == $wId;
      });
    }


    /* TODO */
    protected function filterWorks(&$arg, $filter){
      foreach($arg["workers"] as &$worker){
        $worker['works'] = array_values(array_filter($worker['works'], function($space) use ($worker, $filter) {
          return $filter($space, $worker);
        }));
      }

      $this->filterWorkers($arg, function($worker){ return count($worker['works']) > 0; });
    }

}
