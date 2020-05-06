<?php

class Siren extends Power
{
  public static $id     = SIREN;
  public static $name   = 'Siren';
  public static $title  = 'Alluring Sea Nymph';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Setup: Place the Arrow Token beside the board and orient it in any of the 8 directions to indicate the direction of the Siren's Song.",
   "Your Turn: You may choose not to take your normal turn. Instead, force one or more opponent Workers one space in the direction of the Siren's Song to unoccupied spaces at any level."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  