<?php

class Chaos extends SantoriniPower
{
  public static function getId() {
    return CHAOS;
  }

  public static function getName() {
    return clienttranslate('Chaos');
  }

  public static function getTitle() {
    return clienttranslate('Primordial Nothingness');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Shuffle all unused Simple God Powers into a face-down deck in your play area. Draw the top God Power, and place it face-up beside the deck."),
      clienttranslate("Any Time: You have the Power of the face-up God Power. You must discard your current God Power and draw a new one after any turn in which at least one dome is built. If you run out of God Powers, shuffle them to create a new deck and draw the top one.")
    ];
  }

  public static function getPlayers() {
    return [2, 3, 4];
  }

  public static function getBannedIds() {
    return [];
  }

  public static function isGoldenFleece() {
    return true; 
  }

  /* * */

}
  