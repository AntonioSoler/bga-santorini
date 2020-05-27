<?php

abstract class Utils extends APP_GameClass
{
  public static function filter(&$data, $filter)
  {
    $data = array_values(array_filter($data, $filter));
  }


  /* TODO */
  public static function filterWorkers(&$arg, $filter)
  {
    if ($arg == null) {
      return;
    }

    if (is_array($arg) && array_key_exists('workers', $arg)) {
      Utils::filter($arg['workers'], $filter);
    } else {
      Utils::filter($arg, $filter);
    }
  }

  public static function filterWorkersById(&$arg, $wId, $same = true)
  {
    self::filterWorkers($arg, function (&$worker) use ($wId, $same) {
      return ($same && $worker['id'] == $wId) || (!$same && $worker['id'] != $wId);
    });
  }

  public static function cleanWorkers(&$arg)
  {
    self::filterWorkers($arg, function ($worker) {
      return array_key_exists('works', $worker) && count($worker['works']) > 0;
    });
  }




  /* TODO */
  /*
    // Don't work when you change the array in $filter !!
        $worker['works'] = array_values(array_filter($worker['works'], function(&$space) use ($worker, $filter) {
          return $filter($space, $worker);
        }));
  */
  public static function filterWorks(&$arg, $filter)
  {
    foreach ($arg["workers"] as &$worker) {
      $works = [];

      if (isset($worker['works'])) { // TODO should be useless...
        foreach ($worker['works'] as &$space) {
          if ($filter($space, $worker)) {
            $works[] = $space;
          }
        }
      }
      $worker['works'] = $works;
    }

    self::cleanWorkers($arg);
  }


  public static function &getWorkerOrCreate(&$arg, &$sworker)
  {
    foreach ($arg["workers"] as &$worker) {
      if ($worker['id'] == $sworker['id']) {
        return $worker;
      }
    }

    $arg['workers'][] = &$sworker;
    return $sworker;
  }


  /* TODO */
  public static function checkWork($arg, $wId, $x, $y, $z, $actionArg)
  {
    $workers = array_values(array_filter($arg['workers'], function ($w) use ($wId) {
      return $w['id'] == $wId;
    }));
    if (count($workers) != 1) {
      throw new BgaUserException(_("This worker can't be used"));
    }

    $works = array_values(array_filter($workers[0]['works'], function ($w) use ($x, $y, $z, $actionArg) {
      return $w['x'] == $x && $w['y'] == $y && $w['z'] == $z
        && (is_null($actionArg) || in_array($actionArg, $w['arg']));
    }));
    if (count($works) != 1) {
      throw new BgaUserException(_("You cannot reach this space with this worker"));
    }

    return ['x' => $x, 'y' => $y, 'z' => $z, 'arg' => $actionArg];
  }
}
