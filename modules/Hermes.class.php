<?php

class Hermes extends Power
{
  public static $id     = HERMES;
  public static $name   = 'Hermes';
  public static $title  = 'God of Travel';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Turn: If your Workers do not move up or down, they may each move any number of times (even zero), and then either builds."
  ];
  public static $banned  = [HARPIES];
  public static $players = [2, 3, 4];

}
  