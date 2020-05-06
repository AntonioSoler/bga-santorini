<?php

class Europa extends Power
{
  public static $id     = EUROPA;
  public static $name   = 'Europa & Talus';
  public static $title  = 'Queen & Guardian Automaton';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Place the Talus Token on your God Power card.",
   "End of Your Turn: You may relocate your Talus Token to an unoccupied space neighboring the Worker that moved.",
   "Any Time: All players treat the space containing the Talus Token as if it contains only a dome."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
