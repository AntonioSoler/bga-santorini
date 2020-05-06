<?php

class Jason extends God
{
    public function setup($player)
    {
        $this->game->notifyAllPlayers('message', "Jason: setup() granting 1 more worker on the God Power card", []);
        $this->game->addWorker($player, 'm', 'card');
    }
}
