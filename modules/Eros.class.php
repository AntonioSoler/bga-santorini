<?php

class Eros extends Power
{
  public static $id     = EROS;
  public static $name   = 'Eros';
  public static $title  = 'God of Desire';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Place your Workers anywhere along opposite edges of the board.",
   "Win Condition: You also win if one of your Workers moves to a space neighboring your other Worker and both are on the first level (or the same level in a 3-player game)."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  