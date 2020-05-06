<?php

class Clio extends Power
{
  public static $id     = CLIO;
  public static $name   = 'Clio';
  public static $title  = 'Muse of History';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Your Build: Place a Coin Token on each of the first 3 blocks your Workers build.",
   "Opponent's Turn: Opponents treat spaces containing your Coin Tokens as if they contain only a dome."
  ];
  public static $banned  = [CIRCE, NEMESIS];
  public static $players = [2, 3];

}
  