<?php

class Adonis extends HeroPower
{
  public static function getId() {
    return ADONIS;
  }

  public static function getName() {
    return clienttranslate('Adonis');
  }

  public static function getTitle() {
    return clienttranslate('Devastatingly Handsome');
  }

  public static function getText() {
    return [
      clienttranslate("End of Your Turn: Once, choose an opponent Worker. If possible, that Worker must be neighboring one of your Workers at the end of their next turn.")
    ];
  }

  public static function getBannedIds() {
    return [];
  }

  /* * */

}
  