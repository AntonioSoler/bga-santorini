<?php

class Medusa extends Power
{
  public static $id     = MEDUSA;
  public static $name   = 'Medusa';
  public static $title  = 'Petrifying Gorgon';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "End of Your Turn: If possible, your Workers build in lower neighboring spaces that are occupied by opponent Workers, removing the opponent Workers from the game."
  ];
  public static $banned  = [NEMESIS];
  public static $players = [2, 3, 4];

}
  