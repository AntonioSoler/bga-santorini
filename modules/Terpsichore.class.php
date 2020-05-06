<?php

class Terpsichore extends Power
{
  public static $id     = TERPSICHORE;
  public static $name   = 'Terpsichore';
  public static $title  = 'Muse of Dancing';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Turn: All of your Workers must move, and then all must build."
  ];
  public static $banned  = [NEMESIS, HYPNUS, LIMUS, TARTARUS];
  public static $players = [2, 3, 4];

}
  