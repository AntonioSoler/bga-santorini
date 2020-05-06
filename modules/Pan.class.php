<?php

class Pan extends Power
{
  public static $id     = PAN;
  public static $name   = 'Pan';
  public static $title  = 'God of the Wild';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Win Condition: You also win if your Worker moves down two or more levels."
  ];
  public static $banned  = [HADES];
  public static $players = [2, 3, 4];

}
  