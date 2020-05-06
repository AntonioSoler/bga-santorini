<?php

class Limus extends Power
{
  public static $id     = LIMUS;
  public static $name   = 'Limus';
  public static $title  = 'Goddess of Famine';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Opponent's Turn: Opponent Workers cannot build on spaces neighboring your Workers, unless building a dome to create a Complete Tower."
  ];
  public static $banned  = [TERPSICHORE];
  public static $players = [2, 3, 4];

}
  