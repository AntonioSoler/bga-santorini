<?php

abstract class God extends APP_GameClass {
    /* Factory function to create a god by ID */
    public static function getGod($game, $godId) {
        switch($godId) {
            case JASON:     return new Jason($game);
            default:        return new DummyGod($game);
        }
    }

    public static $id;
    public static $name;
    public static $title;
    public static $power;
    public static $banned;
    public static $players;

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
