<?php

class Charybdis extends Power
{
  public static $id     = CHARYBDIS;
  public static $name   = 'Charybdis';
  public static $title  = 'Whirlpool Monster';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Place 2 Whirlpool Tokens on your God Power card.",
   "End of Your Turn: You may place a Whirlpool Token from your God Power card on any unoccupied space on the board.",
   "Any Time: When both Whirlpool Tokens are in unoccupied spaces, a Worker that moves onto a space containing a Whirlpool Token must immediately move to the other Whirlpool Token's space. This move is considered to be in the same direction as the previous move. When a Whirlpool Token is built on or removed from the board, it is returned to your God Power card."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  