<?php

class Graeae extends Power
{
  public static $id     = GRAEAE;
  public static $name   = 'Graeae';
  public static $title  = 'The Gray Hags';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: When placing your Workers, place 3 of your color.",
   "Your Build: You choose which Worker of yours builds."
  ];
  public static $banned  = [NEMESIS];
  public static $players = [2, 3];

}
  