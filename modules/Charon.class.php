<?php

class Charon extends Power
{
  public static $id     = CHARON;
  public static $name   = 'Charon';
  public static $title  = 'Ferryman to the Underworld';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Move: Before your Worker moves, you may force a neighboring opponent Worker to the space directly on the other side of your Worker, if that space is unoccupied."
  ];
  public static $banned  = [HECATE];
  public static $players = [2, 3, 4];

}
  