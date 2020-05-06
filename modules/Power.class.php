<?php

abstract class Power extends APP_GameClass {
    /* Factory function to create a power by ID */
    public static function getPower($game, $godId) {
        switch($godId) {
            case JASON:     return new Jason($game);
            default:        return new DummyPower($game);
        }
    }

    public static $id;
    public static $name;
    public static $title;
    public static $golden;
    public static $hero;
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
