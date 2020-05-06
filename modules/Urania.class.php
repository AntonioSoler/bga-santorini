<?php

class Urania extends Power
{
  public static $id     = URANIA;
  public static $name   = 'Urania';
  public static $title  = 'Muse of Astronomy';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Turn: When your Worker moves or builds, treat opposite edges and corners as if they are adjacent so that every space has 8 neighbors."
  ];
  public static $banned  = [APHRODITE];
  public static $players = [2, 3, 4];

}
  