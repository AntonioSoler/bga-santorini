<?php

class Jason extends Power
{
  public static $id     = JASON;
  public static $name   = 'Jason';
  public static $title  = 'Leader of the Argonauts';
  public static $hero   = true;
  public static $golden = false;
  public static $power  = [
   "Setup: Take one extra Worker of your color. This is kept on your God Power card until needed.",
   "Your Turn: Once, instead of your normal turn, place your extra Worker on an unoccupied ground-level perimeter space. This Worker then builds."
  ];
  public static $banned  = [];
  public static $players = [2];

}
  