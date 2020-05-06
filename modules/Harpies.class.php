<?php

class Harpies extends Power
{
  public static $id     = HARPIES;
  public static $name   = 'Harpies';
  public static $title  = 'Winged Menaces';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Opponent's Turn: Each time an opponent's Worker moves, it is forced space by space in the same direction until the next space is at a higher level or it is obstructed."
  ];
  public static $banned  = [HERMES, TRITON];
  public static $players = [2, 3, 4];

}
  