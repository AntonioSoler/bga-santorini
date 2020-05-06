<?php

class Moerae extends Power
{
  public static $id     = MOERAE;
  public static $name   = 'Moerae';
  public static $title  = 'Goddesses of Fate';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Take the Map, Shield, and Fate Token. Behind your Shield, secretly select a 2 X 2 square of Fate spaces by placing your Fate Token on the Map. When placing your Workers, place 3 of your color. ",
   "Win Condition: If an opponent Worker attempts to win by moving into one of your Fate spaces, you win instead."
  ];
  public static $banned  = [HECATE, NEMESIS, TARTARUS];
  public static $players = [2, 3];

}
  