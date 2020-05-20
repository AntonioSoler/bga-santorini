<?php

abstract class Utils extends APP_GameClass {
  /* TODO */
  public static function filterWorkers(&$arg, $filter){
    if($arg == null)
      return;

    if(is_array($arg) && array_key_exists('workers', $arg))
      $arg['workers'] = array_values(array_filter($arg['workers'], $filter));
    else
      $arg = array_values(array_filter($arg, $filter));
  }

  public static function filterWorkersById(&$arg, $wId, $same = true){
    self::filterWorkers($arg, function(&$worker) use ($wId, $same){
      return ($same && $worker['id'] == $wId) || (!$same && $worker['id'] != $wId);
    });
  }

  public static function cleanWorkers(&$arg){
      self::filterWorkers($arg, function($worker){ return array_key_exists('works', $worker) && count($worker['works']) > 0; });
  }




  /* TODO */
  /*
    // Don't work when you change the array in $filter !!
        $worker['works'] = array_values(array_filter($worker['works'], function(&$space) use ($worker, $filter) {
          return $filter($space, $worker);
        }));
  */
  public static function filterWorks(&$arg, $filter){
    foreach($arg["workers"] as &$worker){
      $works = [];
      foreach($worker['works'] as &$space){
        if($filter($space, $worker))
          $works[] = $space;
      }
      $worker['works'] = $works;
    }

    self::cleanWorkers($arg);
  }


  public static function &getWorkerOrCreate(&$arg, &$sworker){
    foreach($arg["workers"] as &$worker){
      if($worker['id'] == $sworker['id'])
        return $worker;
    }

    $arg['workers'][] = &$sworker;
    return $sworker;
  }

}
