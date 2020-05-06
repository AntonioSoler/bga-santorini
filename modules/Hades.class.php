<?php

class Hades extends Power
{
  public static $id     = HADES;
  public static $name   = 'Hades';
  public static $title  = 'God of the Underworld';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Opponent's Turn: Opponent Workers cannot move down."
  ];
  public static $banned  = [PAN];
  public static $players = [2, 3, 4];

}
  