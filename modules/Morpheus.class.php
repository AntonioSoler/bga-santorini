<?php

class Morpheus extends Power
{
  public static $id     = MORPHEUS;
  public static $name   = 'Morpheus';
  public static $title  = 'God of Dreams';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Start of Your Turn: Place a block or dome on your God Power card.",
   "Your Build: Your Worker cannot build as normal. Instead, your Worker may build any number of times (even zero) using blocks / domes collected on your God Power card. At any time, any player may exchange a block / dome on the God Power card for dome or a block of a different shape."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  