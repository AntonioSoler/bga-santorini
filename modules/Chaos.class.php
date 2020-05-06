<?php

class Chaos extends Power
{
  public static $id     = CHAOS;
  public static $name   = 'Chaos';
  public static $title  = 'Primordial Nothingness';
  public static $hero   = false;
  public static $golden = true;
  public static $power  = [
   "Setup: Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck.",
   "Any Time: You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one."
  ];
  public static $banned  = [];
  public static $players = [2, 3, 4];

}
  