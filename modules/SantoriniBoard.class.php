<?php

// TODO : description
class SantoriniBoard extends APP_GameClass
{
  public $game;

  public function __construct($game)
  {
    $this->game = $game;
  }

  /*
   * TODO
   */
  public static function getCoords($mixed)
  {
    return ['x' => (int) $mixed['x'], 'y' => (int) $mixed['y'], 'z' => (int) $mixed['z']];
  }

  /*
   * getAvailableWorkers: return all available workers
   * opt params : int $pId -> if specified, return only available workers of corresponding player
   */
  public function getAvailableWorkers($pId = -1)
  {
    return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'desk' AND type = 'worker' " . ($pId == -1 ? "" : "AND player_id = '$pId'"));
  }


  /*
   * getPiece: return all info about a piece
   * params : int $id
   */
  public function getPiece($id)
  {
    return self::getNonEmptyObjectFromDB("SELECT * FROM piece WHERE id = '$id'");
  }


  /*
   * getPlacedPieces: return all pieces on the board
   */
  public function getPlacedPieces()
  {
    return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board'");
  }


  /*
   * getPlacedWorkers: return all placed workers
   * opt params : int $pId -> if specified, return only placed workers of corresponding player
   */
  public function getPlacedWorkers($pId = -1)
  {
    $filter = "";
    if ($pId != -1) {
      $ids = implode(',', $this->game->playerManager->getTeammatesIds($pId));
      $filter = " AND player_id IN ($ids)";
    }

    return self::getObjectListFromDb("SELECT * FROM piece WHERE location = 'board' AND type = 'worker' $filter");
  }


  /*
   * getBoard:
   *   return a 3d matrix reprensenting the board with all the placed pieces
   */
  public static function getBoard()
  {
    // Create an empty 5*5*4 board
    $board = [];
    for ($x = 0; $x < 5; $x++) {
      $board[$x] = [];
      for ($y = 0; $y < 5; $y++)
        $board[$x][$y] = [];
    }

    // Add all placed pieces
    $pieces = self::getPlacedPieces();
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
  public static function getAccessibleSpaces()
  {
    $board = self::getBoard();

    $accessible = [];
    for ($x = 0; $x < 5; $x++)
      for ($y = 0; $y < 5; $y++) {
        $z = 0;
        $blocked = false; // If we see a worker or a dome, the space is not accessible
        // Find next free space above ground
        for (; $z < 4 && !$blocked && array_key_exists($z, $board[$x][$y]); $z++) {
          $p = $board[$x][$y][$z];
          $blocked = ($p['type'] == 'worker' || $p['type'] == 'lvl3');
        }

        if (!$blocked && $z < 4)
          $accessible[] = [
            'x' => $x,
            'y' => $y,
            'z' => $z,
          ];
      }

    return $accessible;
  }


  /*
   * isSameSpace : check distance between two spaces to move/build
   *   TODO
   */
  public static function isSameSpace($a, $b)
  {
    return ($a['x'] == $b['x'] && $a['y'] == $b['y']);
  }

  /*
   * checkDistances : check distance between two spaces to move/build
   *   TODO
   */
  public static function isNeighbour($a, $b, $action)
  {
    $ok = true;

    // Neighbouring : can't be same place, and should be planar coordinate distant
    $ok = $ok && !self::isSameSpace($a, $b);
    $ok = $ok && abs($a['x'] - $b['x']) <= 1 && abs($a['y'] - $b['y']) <= 1;

    // For moving, the new height can't be more than +1
    if ($action == 'moving')
      $ok = $ok && $b['z'] <= $a['z'] + 1;

    return $ok;
  }

  /*
   * getNeighbouringSpaces:
   *   return the list of all accessible neighbouring spaces for either moving a worker or building
   * params:
   *  - mixed $piece : contains all the informations (type, location, player_id) about the piece we use to move/build
   *  - string $action : specifies what kind of action we want to do with this piece (move/build)
   */
  public function getNeighbouringSpaces($piece, $action)
  {
    // Starting from all accessible spaces, and filtering out those too far or too high (for moving only)
    $neighbouring = array_filter(self::getAccessibleSpaces(), function ($space) use ($piece, $action) {
      return self::isNeighbour($piece, $space, $action);
    });

    return array_values($neighbouring);
  }

}
