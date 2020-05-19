<?php

class Jason extends SantoriniHeroPower
{
  public static function getId() {
    return JASON;
  }

  public static function getName() {
    return clienttranslate('Jason');
  }

  public static function getTitle() {
    return clienttranslate('Leader of the Argonauts');
  }

  public static function getText() {
    return [
      clienttranslate("Setup: Take one extra Worker of your color. This is kept on your God Power card until needed."),
      clienttranslate("Your Turn: Once, instead of your normal turn, place your extra Worker on an unoccupied ground-level perimeter space. This Worker then builds.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  