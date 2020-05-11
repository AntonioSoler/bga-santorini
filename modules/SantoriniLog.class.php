<?php

// TODO : description
class SantoriniLog extends APP_GameClass
{
  public static function test()
  {
    self::DbQuery("INSERT INTO log (`round`, `player_id`, `piece_id`, `action`, `action_arg`) VALUES ()");
  }
}
