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
   *   - optional $id : true to include the piece ID in the result array
   */
  public static function getCoords($mixed, $arg = 0, $keepId = false)
  {
    $data = ['x' =>  $mixed['x'], 'y' => $mixed['y'], 'z' => $mixed['z']];
    if ($arg == 1) {
      $data['arg'] = [$mixed['z']];
    }
    if ($arg == 2) {
      $data['arg'] = $mixed['z'];
    }
    if ($keepId) {
      $data['id'] = $mixed['id'];
    }
    Utils::convertIntValues($mixed);
    return $data;
  }


  /*
   * getMsgCoords: return a string to log move/build in the following format: A4 -> A3
   */
  public static function getMsgCoords($worker, $space = null)
  {
    $cols = ['A', 'B', 'C', 'D', 'E'];
    $msg = $cols[$worker['y']] . ($worker['x']  + 1);
    if ($space != null) {
      $msg .= ' -> ' . $cols[$space['y']] . ($space['x']  + 1);
    }
    return $msg;
  }

  public static function addInfo($piece)
  {
    if (is_null($piece)) {
      return $piece;
    }

    $piece['name'] = $piece['type'];
    if ($piece['type'] == 'worker') {
      $piece['name'] = $piece['type_arg'] . $piece['type'];
    } elseif (substr($piece['type'], 0, 5) == 'token') {
      $piece['direction'] = $piece['type_arg'];
    }
    Utils::convertIntValues($piece);
    return $piece;
  }

  /*
   * getPiece: return all info about a piece
   * params : mixed $id, either int or array with ['id'] key
   */
  public function getPiece($mixed)
  {
    $id = null;
    if (is_numeric($mixed)) {
      $id = $mixed;
    } else if (is_array($mixed) && array_key_exists('id', $mixed)) {
      $id = $mixed['id'];
    }
    if ($id == null) {
      return null;
    }
    return self::addInfo(self::getObjectFromDB("SELECT * FROM piece WHERE id = '$id'"));
  }

  /*
   * getPiecesAt: return array the pieces at this x,y,z location.
   * tokens make it possible to have multiple pieces (e.g., Clio coin + worker)
   * params : array $space
   */
  public function getPiecesAt($space)
  {
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND x = {$space['x']} AND y = {$space['y']} AND z = {$space['z']} ORDER BY id"));
  }

  /*
   * getBlocksAt: return array of blocks at this x,y location (for all z, order top down)
   * params : array $space
   */
  public function getBlocksAt($space)
  {
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type IN ('lvl0', 'lvl1', 'lvl2') AND x = {$space['x']} AND y = {$space['y']} ORDER BY z DESC"));
  }

  /*
   * getPiecesByType: return all info about pieces of the given type
   */
  public function getPiecesByType($type, $type_arg = null, $location = null)
  {
    $sql = "SELECT * FROM piece WHERE type = '$type'";
    if ($type_arg) {
      $sql .= " AND type_arg = '$type_arg'";
    }
    if ($location) {
      $sql .= " AND location = '$location'";
    }
    $sql .= " ORDER BY id";
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb($sql));
  }

  /*
   * getPlacedPieces: return all pieces on the board
   * Order by tokens first, then by z-order ascending
   */
  public function getPlacedPieces()
  {
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' ORDER BY (type LIKE 'token%') DESC, z, x, y"));
  }




  /*
   * TODO
   */
  public function playerFilter($pIds = -1, $negate = false)
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

      $filter = empty($ids) ? "FALSE" : "player_id IN (" . implode(',', $ids) . ")";
      $filter = $negate ? " AND NOT ($filter)" : " AND $filter";
    }

    return $filter;
  }

  /*
   * getAvailableWorkers: return all available workers
   * opt params : int $pId -> if specified, return only available workers of corresponding player
   */
  public function getAvailableWorkers($pId = -1)
  {
    $filter = $this->playerFilter($pId);
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'desk' AND type = 'worker' $filter ORDER BY id"));
  }

  /*
   * getRam: return the Ram figure for Golden Fleece variant
   */
  public function getRam()
  {
    return $this->getPiecesByType('ram')[0];
  }


  /*
   * getPlacedWorkers: return all placed workers
   * opt params : int $pId -> if specified, return only placed workers of corresponding player
   */
  public function getPlacedWorkers($pId = -1)
  {
    $filter = $this->playerFilter($pId);
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type = 'worker' $filter ORDER BY id"));
  }

  /*
   * getPlacedTokens: return all placed tokens
   * opt params : int $pId -> if specified, return only placed tokens of corresponding player
   */
  public function getPlacedTokens($pId = -1)
  {
    $filter = $this->playerFilter($pId);
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type LIKE 'token%' $filter ORDER BY id"));
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
   * - automatically filters Clio's protected workers
   * - passive powers like Aphrodite, Harpies, etc. that apply to Clio should not use this function
   *  (see also: getPlacedNotMineWorkers() which does not filter Clio)
   */
  public function getPlacedOpponentWorkers($pId = null)
  {
    $workers = $this->getPlacedWorkers($this->game->playerManager->getOpponentsIds($pId));

    // Clio: Workers on top of a coin are invisible to opponents
    $tokensXY = array_map(function ($token) {
      return [intval($token['x']), intval($token['y'])];
    }, $this->getPiecesByType('tokenCoin', null, 'board'));
    if (!empty($tokensXY)) {
      Utils::filterWorkers($workers, function ($worker) use ($tokensXY) {
        return !in_array([intval($worker['x']), intval($worker['y'])], $tokensXY);
      });
    }

    return $workers;
  }

  /*
   * getPlacedWorkers: return all placed workers except those of the active player
   */
  public function getPlacedNotMineWorkers($pId = null)
  {
    $filter = $this->playerFilter($this->game->playerManager->getTeammatesIds($pId), true);
    return array_map('SantoriniBoard::addInfo', self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type = 'worker' $filter ORDER BY id"));
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
  public function getAccessibleSpaces($action = null, $powerIds = [])
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
          if ($p['type'] == 'tokenCoin' && in_array(CLIO, $powerIds)) {
            break;
          }
          $blocked = $p['type'] == 'worker' || $p['type'] == 'ram' || $p['type'] == 'lvl3' || $p['type'] == 'tokenTalus' || $p['type'] == 'tokenCoin';
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

  // This supposed that the normal move is at distance at most one, might not work with some powers
  public function isDiagonal($a, $b)
  {
    return in_array($a['direction'], [NE, SE, SW, NW]);
  }


  /*
   * isNeighbour : check distance between two spaces to move/build
   */
  public static function isNeighbour($a, $b, $action = null, $powerIds = [])
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
   * getDirection : get the corresponding direction of a move/build
   */
  public static function getDirection($a, $b, $powerIds = [])
  {
    if (in_array(URANIA, $powerIds) && !self::isNeighbour($a, $b)) {
      $dx = abs($a['x'] - $b['x']) <= 1 ? 0 : ($a['x'] < $b['x'] ? 1 : -1);
      $dy = abs($a['y'] - $b['y']) <= 1 ? 0 : ($a['y'] < $b['y'] ? 1 : -1);
      $a['x'] = $a['x'] + 5 * $dx;
      $a['y'] = $a['y'] + 5 * $dy;
    }

    $found = false;
    foreach (DIRECTIONS as $d => $delta) {
      if ($a['x'] + $delta['x'] == $b['x'] && $a['y'] + $delta['y'] == $b['y']) {
        if ($found) {
          throw new BgaVisibleSystemException("getDirection: Two directions found");
        }
        $found = $d;
      }
    }
    if ($found === false) {
      throw new BgaVisibleSystemException("getDirection: No direction found");
    }

    return $found;
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
    $spaces = $this->getAccessibleSpaces($action, $powerIds);
    Utils::filter($spaces, function ($space) use ($piece, $action, $powerIds) {
      return $this->isNeighbour($piece, $space, $action, $powerIds);
    });
    array_walk($spaces, function (&$space) use ($piece, $powerIds) {
      $space['direction'] = $this->getDirection($piece, $space, $powerIds);
    });
    return $spaces;
  }


  /*
   * getPieceCount: Return the number of blocks and domes on the board
   */
  public function getPieceCount()
  {
    return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM piece WHERE location = 'board' AND type LIKE 'lvl%'"));
  }

  /*
   * getWorkerCount: Return the number of workers on the board
   */
  public function getWorkerCount()
  {
    return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM piece WHERE location = 'board' AND type = 'worker'"));
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

    if (count($spaces) == 1) {
      $spaces[0]['direction'] = $this->getDirection($worker, $worker2);
      return $spaces[0];
    }
    return null;
  }


  /*
   * getNextSpace: Return the space after a space given a direction
   */
  public function getNextSpace($space, $accessibleSpaces)
  {
    $x = $space['x'] + DIRECTIONS[$space['direction']]['x'];
    $y = $space['y'] + DIRECTIONS[$space['direction']]['y'];
    $spaces = array_values(array_filter($accessibleSpaces, function ($space) use ($x, $y) {
      return $space['x'] == $x && $space['y'] == $y;
    }));

    if (count($spaces) == 1) {
      $spaces[0]['direction'] = $space['direction'];
      return $spaces[0];
    }
    return null;
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
