<?php

abstract class God extends APP_GameClass {
    /* Factory function to create a god by ID */
    public static function getGod($game, $godId) {
        switch($godId) {
            case JASON:     return new Jason($game);
            default:        return new DummyGod($game);
        }
    }

    protected $game;

    public function __construct($game) {
        $this->game = $game;
    }

    public function setup($player) {}
    public function beforeMove() {}
    public function argMove() {}
    public function move() {}
    public function beforeBuild() {}
    public function argBuild() {}
    public function build() {}
    public function endTurn() {}
    public function winCondition() {}
}
