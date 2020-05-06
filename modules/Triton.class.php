<?php

class Triton extends Power
{
  public static $id     = TRITON;
  public static $name   = 'Triton';
  public static $title  = 'God of the Waves';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Your Move: Each time your Worker moves into a perimeter space, it may immediately move again."
  ];
  public static $banned  = [HARPIES];
  public static $players = [2, 3, 4];

}
  