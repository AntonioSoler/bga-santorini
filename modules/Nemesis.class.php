<?php

class Nemesis extends Power
{
  public static $id     = NEMESIS;
  public static $name   = 'Nemesis';
  public static $title  = 'Goddess of Retribution';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "End of Your Turn: If none of an opponent's Workers neighbor yours, you may force as many of your opponent's Workers as possible to take the spaces you occupy, and vice versa."
  ];
  public static $banned  = [CLIO, GAEA, GRAEAE, MOERAE, APHRODITE, BIA, MEDUSA, TERPSICHORE, THESEUS];
  public static $players = [2, 3, 4];

}
  