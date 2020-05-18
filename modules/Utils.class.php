<?php

abstract class Utils extends APP_GameClass {
  /* TODO */
  public static function filterWorkers(&$arg, $filter){
    if(array_key_exists('workers', $arg))
      $arg['workers'] = array_values(array_filter($arg['workers'], $filter));
    else
      $arg = array_values(array_filter($arg, $filter));
  }

  public static function filterWorkersById(&$arg, $wId){
    self::filterWorkers($arg, function($worker) use ($wId){
      return $worker['id'] == $wId;
    });
  }

  public static function cleanWorkers(&$arg){
      self::filterWorkers($arg, function($worker){ return array_key_exists('works', $worker) && count($worker['works']) > 0; });
  }




  /* TODO */
  public static function filterWorks(&$arg, $filter){
    foreach($arg["workers"] as &$worker){
      $worker['works'] = array_values(array_filter($worker['works'], function($space) use ($worker, $filter) {
        return $filter($space, $worker);
      }));
    }

    self::cleanWorkers($arg);
  }
}
