<?php

class Theseus extends Power
{
  public static $id     = THESEUS;
  public static $name   = 'Theseus';
  public static $title  = 'Slayer of the Minotaur';
  public static $hero   = true;
  public static $golden = false;
  public static $power  = [
   "End of Your Turn: Once, if any of your Workers is exactly 2 levels below any neighboring opponent Workers, remove one of those opponent Workers from play."
  ];
  public static $banned  = [NEMESIS];
  public static $players = [2];

}
  