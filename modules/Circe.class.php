<?php

class Circe extends Power
{
  public static $id     = CIRCE;
  public static $name   = 'Circe';
  public static $title  = 'Divine Enchantress';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Start of Your Turn: If an opponent's Workers do not neighbor each other, you alone have use of their power until your next turn."
  ];
  public static $banned  = [CLIO, HECATE];
  public static $players = [2];

}
  