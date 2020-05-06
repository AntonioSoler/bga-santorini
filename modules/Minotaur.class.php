<?php

class Minotaur extends Power
{
  public static $id     = MINOTAUR;
  public static $name   = 'Minotaur';
  public static $title  = 'Bull-headed Monster';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Move: Your Worker may move into an opponent Worker's space, if their Worker can be forced one space straight backwards to an unoccupied space at any level."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  