<?php

class Medea extends Power
{
  public static $id     = MEDEA;
  public static $name   = 'Medea';
  public static $title  = 'Powerful Sorceress';
  public static $hero   = true;
  public static $golden = false;
  public static $power  = [
   "End of Your Turn: Once, remove one block from under any number of Workers neighboring your unmoved Worker. You also remove any Tokens on the blocks."
  ];
  public static $banned  = [];
  public static $players = [2];

}
  