<?php

class Ares extends Power
{
  public static $id     = ARES;
  public static $name   = 'Ares';
  public static $title  = 'God of War';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "End of Your Turn: You may remove an unoccupied block (not dome) neighboring your unmoved Worker. You also remove any Tokens on the block."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  