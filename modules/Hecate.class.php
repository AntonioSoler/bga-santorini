<?php

class Hecate extends Power
{
  public static $id     = HECATE;
  public static $name   = 'Hecate';
  public static $title  = 'Goddess of Magic';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Take the Map, Shield, and 2 Worker Tokens. Hide the Map behind the Shield and secretly place your Worker Tokens on the Map to represent the location of your Workers on the game board. Place your Workers last.",
   "Your Turn: Move a Worker Token on the Map as if it were on the game board. Build on the game board, as normal.",
   "Any Time: If an opponent attempts an action that would not be legal due to the presence of your secret Workers, their action is cancelled and they lose the rest of their turn. When possible, use their power on their behalf to make their turns legal without informing them."
  ];
  public static $banned  = [CHARON, CIRCE, MOERAE, TARTARUS];
  public static $players = [2, 3];

}
  