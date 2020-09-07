<?php

abstract class Presets extends APP_GameClass
{
  public static $presets = [
    "20200907" => [ HERACLES, HYDRA],
//    "20200914" => [ HERACLES, HYDRA],
//    "20200921" => [ HERACLES, HYDRA],
  ];


  public static function isSupported($powerId)
  {
    $today = date("Ymd");
    foreach(self::$presets as $starting => $powers){
      if(strcmp($today, $starting) >= 0){
        return in_array($powerId, $powers);
      }
    }
  }
}
?>
