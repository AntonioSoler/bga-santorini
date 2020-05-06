<?php

class Gaea extends Power
{
  public static $id     = GAEA;
  public static $name   = 'Gaea';
  public static $title  = 'Goddess of the Earth';
  public static $hero   = false;
  public static $golden = false;
  public static $power  = [
   "Setup: Take 2 extra Workers of your color. These are kept on your God Power card until needed.",
   "Any Build: When a Worker builds a dome, Gaea may immediately place a Worker from her God Power card onto a ground-level space neighboring the dome."
  ];
  public static $banned  = [ATLAS, NEMESIS, SELENE];
  public static $players = [2, 3];

}
  