<?php

class DummyGod extends God
{
    public function setup($player)
    {
        $this->game->notifyAllPlayers('message', "DummyGod: setup()", []);
    }

    public function beforeMove()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: beforeMove()", []);
    }

    public function argMove()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: argMove()", []);
    }

    public function move()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: move()", []);
    }

    public function beforeBuild()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: beforeBuild()", []);
    }

    public function argBuild()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: argBuild()", []);
    }

    public function build()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: build()", []);
    }

    public function endTurn()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: endTurn()", []);
    }

    public function winCondition()
    {
        $this->game->notifyAllPlayers('message', "DummyGod: winCondition()", []);
    }
}
