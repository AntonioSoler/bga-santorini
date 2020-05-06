<?php

class Bia extends Power
{
  public static $id     = BIA;
  public static $name   = 'Bia';
  public static $title  = 'Goddess of Violence';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Setup: Place your Workers first.",
   "Your Move: If your Worker moves into a space and the next space in the same direction is occupied by an opponent Worker, the opponent's Worker is removed from the game."
  ];
  public static $banned  = [NEMESIS, TARTARUS];
  public static $players = [2, 3, 4];

}
  