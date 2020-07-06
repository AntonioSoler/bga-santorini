<?php

/*
 * SantoriniBoard: all utility functions concerning space on the board are here
 */
class SantoriniBoard extends APP_GameClass
{
  public $game;
  public function __construct($game)
  {
    $this->game = $game;
  }

  /*
   * getCoords: return an array with only 'x', 'y', 'z' keys parsed as int
   *   - optional $arg :
   *       * 0 : don't add any field arg
   *       * 1 : arg is filled with singleton [z] (useful eg in argPlayerBuild)
   *       * 2 : arg is set to z (useful when building)
   */
  public static function getCoords($mixed, $arg = 0)
  {
    $data = ['x' => (int) $mixed['x'], 'y' => (int) $mixed['y'], 'z' => (int) $mixed['z']];
    if ($arg == 1) {
      $data['arg'] = [$mixed['z']];
    }
    if ($arg == 2) {
      $data['arg'] = $mixed['z'];
    }

    return $data;
  }


  /*
   * getMsgCoords: return a string to log move/build in the following format: A4 -> A3
   */
  public static function getMsgCoords($worker, $space = null)
  {
    $cols = ['A', 'B', 'C', 'D', 'E'];
    $msg = $cols[$worker['y']] . ((int) $worker['x']  + 1);
    if ($space != null) {
      $msg .= ' -> ' . $cols[$space['y']] . ((int) $space['x']  + 1);
    }
    return $msg;
  }


  /*
   * getPiece: return all info about a piece
   * params : int $id
   */
  public function getPiece($id)
  {
    return self::getNonEmptyObjectFromDB("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE id = '$id'");
  }

  /*
   * getPieceAt: return all info about a piece at a location
   * params : array $space
   */
  public function getPieceAt($space)
  {
    return self::getObjectFromDB("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE location = 'board' AND x = {$space['x']} AND y = {$space['y']} AND z = {$space['z']}");
  }


  /*
   * getPlacedPieces: return all pieces on the board
   */
  public function getPlacedPieces()
  {
    return self::getObjectListFromDb("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE location = 'board'");
  }




  /*
   * TODO
   */
  public function playerFilter($pIds = -1)
  {
    $filter = "";
    if ($pIds != -1) {
      if (!is_array($pIds)) {
        $pIds = [$pIds];
      }
      $ids = [];
      foreach ($pIds as $pId) {
        $ids = array_merge($ids, $this->game->playerManager->getTeammatesIds($pId));
      }

      $filter = empty($ids) ? "AND FALSE" : (" AND player_id IN (" . implode(',', $ids) . ")");
    }

    return $filter;
  }

  /*
   * getAvailableWorkers: return all available workers
   * opt params : int $pId -> if specified, return only available workers of corresponding player
   */
  public function getAvailableWorkers($pId = -1)
  {
    return self::getObjectListFromDb("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE location = 'desk' AND type = 'worker' " . $this->playerFilter($pId));
  }

  /*
   * getRam: return the Ram figure for Golden Fleece variant
   */
  public function getRam()
  {
    return self::getObjectFromDb("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE type = 'ram'");
  }


  /*
   * getPlacedWorkers: return all placed wor!kers
   * opt params : int $pId -> if specified, return only placed workers of corresponding player
   */
  public function getPlacedWorkers($pId = -1)
  {
    return self::getObjectListFromDb("SELECT *, CONCAT(type_arg, type) AS name FROM piece WHERE location = 'board' AND type = 'worker' " . $this->playerFilter($pId));
  }


  /*
   * getPlacedActiveWorkers: return all placed workers of active player
   */
  public function getPlacedActiveWorkers($type = null)
  {
    $workers = $this->getPlacedWorkers($this->game->playerManager->getTeammatesIds());
    if ($type == null) {
      return $workers;
    }

    return array_values(array_filter($workers, function ($worker) use ($type) {
      return $worker['type_arg'][0] == $type;
    }));
  }

  /*
   * getPlacedOpponentWorkers: return all placed workers of opponents of active player
   */
  public function getPlacedOpponentWorkers($pId = null)
  {
    return $this->game->board->getPlacedWorkers($this->game->playerManager->getOpponentsIds($pId));
  }



  /*
   * getBoard:
   *   return a 3d matrix reprensenting the board with all the placed pieces
   */
  public function getBoard()
  {
    // Create an empty 5*5*4 board
    $board = [];
    for ($x = 0; $x < 5; $x++) {
      $board[$x] = [];
      for ($y = 0; $y < 5; $y++) {
        $board[$x][$y] = [];
      }
    }

    // Add all placed pieces
    $pieces = $this->getPlacedPieces();
    for ($i = 0; $i < count($pieces); $i++) {
      $p = $pieces[$i];
      $board[$p['x']][$p['y']][$p['z']] = $p;
    }

    return $board;
  }


  /*
   * getAccessibleSpaces:
   *   return the list of all accessible spaces for either placing a worker, moving or building
   */
  public function getAccessibleSpaces($action = null)
  {
    $board = $this->getBoard();

    $accessible = [];
    for ($x = 0; $x < 5; $x++) {
      for ($y = 0; $y < 5; $y++) {
        $z = 0;
        $blocked = false; // If we see a worker, ram, or dome, the space is not accessible
        // Find next free space above ground
        for (; $z < 4 && !$blocked && array_key_exists($z, $board[$x][$y]); $z++) {
          $p = $board[$x][$y][$z];
          $blocked = ($p['type'] == 'worker' || $p['type'] == 'ram' || $p['type'] == 'lvl3');
        }

        if ($blocked || $z > 3) {
          continue;
        }

        // Add the space to accessible
        $space = [
          'x' => $x,
          'y' => $y,
          'z' => $z,
          'arg' => null,
        ];
        if ($action == "build") {
          $space['arg'] = [$z];
        }

        $accessible[] = $space;
      }
    }

    return $accessible;
  }


  /*
   * isSameSpace: check if two spaces are the same, upto z-translation
   */
  public static function isSameSpace($a, $b)
  {
    return $a['x'] == $b['x'] && $a['y'] == $b['y'];
  }

  /*
   * isPerimeter: TODO
   */
  public function isPerimeter($space)
  {
    return $space['x'] == 0 || $space['x'] == 4 || $space['y'] == 0 || $space['y'] == 4;
  }

  public function isCorner($space)
  {
    return ($space['x'] == 0 || $space['x'] == 4) && ($space['y'] == 0 || $space['y'] == 4);
  }

  public function isDiagonal($a, $b)
  {
    $diffX = abs($a['x'] - $b['x']);
    $diffY = abs($a['y'] - $b['y']);
    return $diffX != 0 && $diffX == $diffY;
  }


  /*
   * isNeighbour : check distance between two spaces to move/build
   */
  public function isNeighbour($a, $b, $action = null, $powerIds = [])
  {
    $ok = true;

    // Neighbouring : can't be same place, and should be planar coordinate distant
    $ok = $ok && !self::isSameSpace($a, $b);
    if (in_array(URANIA, $powerIds)) {
      $ok = $ok && min(abs($a['x'] - $b['x']), abs($a['x'] + 5 - $b['x']), abs($a['x'] - $b['x'] - 5)) <= 1
        && min(abs($a['y'] - $b['y']), abs($a['y'] + 5 - $b['y']), abs($a['y'] - $b['y'] - 5)) <= 1;
    } else {
      $ok = $ok && abs($a['x'] - $b['x']) <= 1 && abs($a['y'] - $b['y']) <= 1;
    }

    // For moving, the new height can't be more than +1
    if ($action == 'move') {
      if (in_array(PEGASUS, $powerIds)) {
        $ok = $ok; // any height
      } else if (in_array(BELLEROPHON, $powerIds)) {
        $ok =  $ok && $b['z'] <= $a['z'] + 2;
      } else {
        $ok = $ok && $b['z'] <= $a['z'] + 1;
      }
    }

    return $ok;
  }

  /*
   * getNeighbouringSpaces:
   *   return the list of all accessible neighbouring spaces for either moving a worker or building
   * params:
   *  - mixed $piece : contains all the informations (type, location, player_id) about the piece we use to move/build
   *  - string $action : specifies what kind of action we want to do with this piece (move/build)
   */
  public function getNeighbouringSpaces($piece, $action = null, $powerIds = [])
  {
    // Starting from all accessible spaces, and filtering out those too far or too high (for moving only)
    return array_values(array_filter($this->getAccessibleSpaces($action), function ($space) use ($piece, $action, $powerIds) {
      return $this->isNeighbour($piece, $space, $action, $powerIds);
    }));
  }


  /*
   * getCompleteTowerCount: Return the number of Complete Towers (domes on level 3)
   */
  public function getCompleteTowerCount()
  {
    return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM piece WHERE location = 'board' AND z = '3' AND type = 'lvl3'"));
  }


  /*
   * getSpaceBehind: Return the space behind a piece ($worker2) from another piece ($worker1)
   */
  public function getSpaceBehind(&$worker, &$worker2, &$accessibleSpaces)
  {
    $x = 2 * $worker2['x'] - $worker['x'];
    $y = 2 * $worker2['y'] - $worker['y'];
    $spaces = array_values(array_filter($accessibleSpaces, function ($space) use ($x, $y) {
      return $space['x'] == $x && $space['y'] == $y;
    }));

    return (count($spaces) == 1) ? $spaces[0] : null;
  }


  /*##########################
  ######### Setters ##########
  ##########################*/

  /*
   * setPieceAt: update location of an already existing piece
   */
  public function setPieceAt($piece, $space, $location = 'board')
  {
    self::DbQuery("UPDATE piece SET location = '$location', x = {$space['x']}, y = {$space['y']}, z = {$space['z']} WHERE id = {$piece['id']}");
  }
}
