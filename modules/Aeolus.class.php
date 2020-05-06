<?php

class Aeolus extends Power
{
  public static $id     = AEOLUS;
  public static $name   = 'Aeolus';
  public static $title  = 'God of the Winds';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Setup: Place the Wind Token beside the board and orient it in any of the 8 directions to indicate which direction the Wind is blowing.",
   "End of Your Turn: Orient the Wind Token to any of the the eight directions.",
   "Any Move: Workers cannot move directly into the Wind."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  