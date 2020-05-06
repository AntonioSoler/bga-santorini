<?php

class Tartarus extends Power
{
  public static $id     = TARTARUS;
  public static $name   = 'Tartarus';
  public static $title  = 'God of the Abyss';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Take the Map, Shield, and one Abyss Token. Place your Workers first. After all players' Workers are placed, hide the Map behind the Shield and secretly place your Abyss Token on an unoccupied space. This space is the Abyss.",
   "Lose Condition: If any player's Worker enters the Abyss, they immediately lose. Workers cannot win by entering the Abyss."
  ];
  public static $banned  = [BIA, HECATE, MOERAE, TERPSICHORE];
  public static $players = [2];

}
  