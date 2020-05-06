<?php

class Jason extends God
{
  public static $id   = JASON;
  public static $name = 'Jason';
  public static $title = 'Leader of the Argonauts';
  public static $power = [
    'Setup: Take one extra Worker of your color. This is kept on your God Power card until needed.',
    'Your Turn: Once, instead of your normal turn, place your extra Worker on an unoccupied ground-level perimeter space. This Worker then builds.',
  ];
  public static $banned = [];
  public static $players = [2];


  public function setup($player)
  {
    $this->game->notifyAllPlayers('message', "Jason: setup() granting 1 more worker on the God Power card", []);
    $this->game->addWorker($player, 'm', 'card');
  }
}
