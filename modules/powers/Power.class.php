<?php

abstract class Power extends APP_GameClass {

    protected $game;
    protected $playerId;

    public function __construct($game, $playerId) {
      $this->game = $game;
      $this->playerId = $playerId;
    }

    public function setup($player) {}

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
}
