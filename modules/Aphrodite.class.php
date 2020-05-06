<?php

class Aphrodite extends Power
{
  public static $id     = APHRODITE;
  public static $name   = 'Aphrodite';
  public static $title  = 'Goddess of Love';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Any Move: If an opponent Worker starts its turn neighboring one of your Workers, its last move must be to a space neighboring one of your Workers."
  ];
  public static $banned  = [NEMESIS, URANIA];
  public static $players = [2, 4];

}
  