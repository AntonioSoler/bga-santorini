<?php

class Hypnus extends Power
{
  public static $id     = HYPNUS;
  public static $name   = 'Hypnus';
  public static $title  = 'God of Sleep';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Start of Opponent's Turn: If one of your opponent's Workers is higher than all of their others, it cannot move."
  ];
  public static $banned  = [TERPSICHORE];
  public static $players = [2, 3, 4];

}
  