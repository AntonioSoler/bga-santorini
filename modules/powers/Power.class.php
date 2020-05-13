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
    public function playerMove($wId, $x, $y, $z) { return false; }
    public function stateAfterMove(){ return null; }

    public function beforeBuild() {}
    public function argBuild() {}
    public function build() {}
    public function endTurn() {}
    public function winCondition() {}
}
