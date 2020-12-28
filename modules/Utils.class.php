<?php

abstract class Utils extends APP_GameClass
{
  public static function filter(&$data, $filter)
  {
    $data = array_values(array_filter($data, $filter));
  }

  public static function search($data, $filter)
  {
    Utils::filter($data, $filter);
    return (count($data) >= 1) ? $data[0] : null;
  }

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
      return is_array($wId)
        ? (($same && in_array($worker['id'], $wId)) || (!$same && !in_array($worker['id'], $wId)))
        : (($same && $worker['id'] == $wId) || (!$same && $worker['id'] != $wId));
    });
  }

  public static function cleanWorkers(&$arg)
  {
    self::filterWorkers($arg, function ($worker) {
      return array_key_exists('works', $worker) && count($worker['works']) > 0;
    });
  }

  // For Selene, Heracles, Polyphemus
  public static function updateWorkerArgsBuildDome(&$worker, $add)
  {
    foreach ($worker['works'] as &$work) {
      if ($add) {
        if (!in_array(3, $work['arg'])) {
          $work['arg'][] = 3;
        }
      } else {
        $work['arg'] = [3];
        $work['dialog'] = true;
      }
    }
  }


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

      if (isset($worker['works'])) {
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

  public static function filterWorksUnlessMine(&$arg, $workers, $filter)
  {
    $workersIds = array_map(function ($worker) {
      return $worker['id'];
    }, $workers);
    Utils::filterWorks($arg, function (&$space, &$worker) use ($filter, $workersIds) {
      return in_array($worker['id'], $workersIds) || $filter($space, $worker);
    });
  }

  public static function addWork(&$worker, $space)
  {
    $coords = SantoriniBoard::getCoords($space, 0, true);
    $coords['direction'] = SantoriniBoard::getDirection($worker, $space);
    $worker['works'][] = $coords;
  }

  public static function mergeWorkers(&$arg, $workers)
  {
    foreach ($workers as $worker) {
      $found = false;
      foreach ($arg['workers'] as &$worker2) {
        if ($worker['id'] == $worker2['id']) {
          $found = true;
          foreach ($worker['works'] as $work) {
            if (!in_array($work, $worker2['works']))
              $worker2['works'][] = $work;
          }
        }
      }

      if (!$found) {
        $arg['workers'][] = $worker;
      }
    }
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

  public static function checkWork($arg, $wId, $x, $y, $z, $actionArg)
  {
    $worker = Utils::search($arg['workers'], function ($w) use ($wId) {
      return $w['id'] == $wId;
    });
    if (is_null($worker)) {
      throw new BgaUserException(_("This worker can't be used"));
    }

    $work = Utils::search($worker['works'], function ($w) use ($x, $y, $z, $actionArg) {
      return $w['x'] == $x && $w['y'] == $y && $w['z'] == $z
        && (is_null($actionArg) || in_array($actionArg, $w['arg']));
    });
    if (is_null($work)) {
      throw new BgaUserException(_("You cannot reach this space with this worker"));
    }

    $work['arg'] = $actionArg;
    return $work;
  }

  public static function getMoveIds($works)
  {
    return array_map(function ($work) {
      return intval($work['moveId']);
    }, $works);
  }

  public static function getPowerIds($powers)
  {
    return array_map(function ($power) {
      return intval($power->getId());
    }, $powers);
  }

  /* convertIntValues: change the values for x,y,z and other numeric keys to int (instead of string) */
  public static function convertIntValues(&$array)
  {
    array_walk($array, function (&$value, $key) {
      if (in_array($key, ['id', 'x', 'y', 'z', 'arg', 'direction', 'visibility'])) {
        $value = (int) $value;
      }
    });
  }
}
