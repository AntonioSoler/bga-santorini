<?php

// Adjacency constants
define('LEFT_TOP', 1);
define('RIGHT_TOP', 2);
define('RIGHT_MIDDLE', 3);
define('RIGHT_BOTTOM', 4);
define('LEFT_BOTTOM', 5);
define('LEFT_MIDDLE', 6);

// Contain mode constants
define('CONTAINS_ALL', 1);
define('CONTAINS_ANY', 2);

class santoriniSpace extends APP_GameClass
{
    public $id;
    public $x;
    public $y;
    public $z;
    public $r;
    public $face;
    public $tile_id;
    public $subface;
    public $tile_player_id;
    public $tile_type;
    public $bldg_player_id;
    public $bldg_type;

    public function __construct($row)
    {
        $this->id = (int) @$row['id'];
        $this->x = (int) $row['x'];
        $this->y = (int) $row['y'];
        $this->z = (int) $row['z'];
        $this->r = (int) $row['r'];
        $this->face = (int) @$row['face'];
        $this->tile_id = (int) @$row['tile_id'];
        $this->subface = (int) @$row['subface'];
        $this->tile_player_id = (int) @$row['tile_player_id'];
        $this->tile_type = @$row['tile_type'];
        $this->bldg_player_id = (int) @$row['bldg_player_id'];
        $this->bldg_type = (int) @$row['bldg_type'];
    }

    public function __toString()
    {
        return $this->toXYZ();
    }

    public function toXY()
    {
        return '['. $this->x . ',' . $this->y .']';
    }

    public function toXYZ()
    {
        return '[' . $this->x . ',' . $this->y . ',' . $this->z . ']';
    }

    // Does this space exist on the board?
    // False for imaginary (empty) and pending (not yet saved) spaces
    public function exists()
    {
        return !!$this->tile_player_id;
    }

    // Is this space avaialble for placing a new building?
    public function canBuild()
    {
        return $this->exists() && !$this->bldg_type && $this->face !== VOLCANO;
    }
}

class santoriniBoard extends APP_GameClass implements JsonSerializable
{
    private $board = array();
    private $top = array();
    private $buildings = array();

    public function __construct()
    {
        $rows = self::getObjectListFromDB('SELECT id, x, y, z, r, face, tile_id, subface, tile_player_id, t.card_type AS tile_type, bldg_player_id, bldg_type FROM board b LEFT OUTER JOIN tile t ON (b.subface = 0 AND b.tile_id = t.card_id) ORDER BY x, y, z');
        foreach ($rows as $row) {
            $space = new santoriniSpace($row);
            $x = $space->x;
            $y = $space->y;
            $z = $space->z;
            $this->board[$x][$y][$z] = $space;
            $this->top[$x][$y] = $z;
            if ($space->bldg_player_id) {
                $this->buildings[$space->bldg_player_id][] = $space;
            }
        }
    }

    public function jsonSerialize()
    {
        return $this->getSpaces();
    }

    public function empty()
    {
        return empty($this->board);
    }

    public function getSpaces()
    {
        $spaces = array();
        foreach ($this->board as $x => $xrow) {
            foreach ($xrow as $y => $yrow) {
                foreach ($yrow as $z => $space) {
                    $spaces[] = $space;
                }
            }
        }
        return $spaces;
    }

    public function getSpace($x, $y, $z)
    {
        if (array_key_exists($x, $this->board) && array_key_exists($y, $this->board[$x]) && array_key_exists($z, $this->board[$x][$y])) {
            return $this->board[$x][$y][$z];
        }

        // Return an imaginary (empty) space
        return new santoriniSpace(array(
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'r' => 0,
        ));
    }

    public function getSpaceOnTop($x, $y)
    {
        $z = 1;
        if (array_key_exists($x, $this->top) && array_key_exists($y, $this->top[$x])) {
            $z = $this->top[$x][$y];
        }
        return $this->getSpace($x, $y, $z);
    }

    public function getSpaceBelow($space)
    {
        return $this->getSpace($space->x, $space->y, $space->z - 1);
    }

    public function getSpaceAbove($space)
    {
        return $this->getSpace($space->x, $space->y, $space->z + 1);
    }

    public function getSpaceAdjacents($space)
    {
        $xmod = abs($space->y % 2);
        return array(
            LEFT_TOP => $this->getSpace($xmod + $space->x - 1, $space->y - 1, $space->z),
            RIGHT_TOP => $this->getSpace($xmod + $space->x, $space->y - 1, $space->z),
            RIGHT_MIDDLE => $this->getSpace($space->x + 1, $space->y, $space->z),
            RIGHT_BOTTOM => $this->getSpace($xmod + $space->x, $space->y + 1, $space->z),
            LEFT_BOTTOM => $this->getSpace($xmod + $space->x - 1, $space->y + 1, $space->z),
            LEFT_MIDDLE => $this->getSpace($space->x - 1, $space->y, $space->z),
        );
    }

    public function getAdjacentsOnTop($space)
    {
        $xmod = abs($space->y % 2);
        return array(
            LEFT_TOP => $this->getSpaceOnTop($xmod + $space->x - 1, $space->y - 1),
            RIGHT_TOP => $this->getSpaceOnTop($xmod + $space->x, $space->y - 1),
            RIGHT_MIDDLE => $this->getSpaceOnTop($space->x + 1, $space->y),
            RIGHT_BOTTOM => $this->getSpaceOnTop($xmod + $space->x, $space->y + 1),
            LEFT_BOTTOM => $this->getSpaceOnTop($xmod + $space->x - 1, $space->y + 1),
            LEFT_MIDDLE => $this->getSpaceOnTop($space->x - 1, $space->y),
        );
    }

    public function getSpaceRotations($space)
    {
        $adjacent = $this->getSpaceAdjacents($space);
        $rotations = array(
            0 => array($space, $adjacent[LEFT_BOTTOM], $adjacent[RIGHT_BOTTOM]),
            60 => array($space, $adjacent[LEFT_MIDDLE], $adjacent[LEFT_BOTTOM]),
            120 => array($space, $adjacent[LEFT_TOP], $adjacent[LEFT_MIDDLE]),
            180 => array($space, $adjacent[RIGHT_TOP], $adjacent[LEFT_TOP]),
            240 => array($space, $adjacent[RIGHT_MIDDLE], $adjacent[RIGHT_TOP]),
            300 => array($space, $adjacent[RIGHT_BOTTOM], $adjacent[RIGHT_MIDDLE]),
        );
        return $rotations;
    }

    // Return the 3 spaces that form this tile
    public function getSpacesForTile($x, $y, $z, $r, $tile_type)
    {
        list($space0, $space1, $space2) = $this->getSpaceRotations($this->getSpace($x, $y, $z))[$r];
        $space0->r = $r;
        $space0->face = VOLCANO;
        $space1->r = $r;
        $space1->face = (int) substr($tile_type, 0, 1);
        $space2->r = $r;
        $space2->face = (int) substr($tile_type, 1, 1);
        return array($space0, $space1, $space2);
    }

    public function getSettlements()
    {
        $settlements = array();
        foreach ($this->buildings as $player_id => $spaces) {
            $playerSettlements = array();
            $remaining = $spaces;
            while (!empty($remaining)) {
                // Begin a new settlement with the next building
                $settlement = array(array_shift($remaining));
                $this->chainSettlement($settlement, $remaining);
                $playerSettlements[] = $settlement;
            }
            $settlements[$player_id] = $playerSettlements;
        }
        return $settlements;
    }

    private function chainSettlement(&$settlement, &$remaining)
    {
        $end = end($settlement);
        $adjacents = $this->getAdjacentsOnTop($end);
        foreach ($remaining as $key => $r) {
            if ($this->containsXY($adjacents, array($r), CONTAINS_ANY)) {
                // Put this building into the settlement and recurse again
                unset($remaining[$key]);
                $settlement[] = $r;
                $this->chainSettlement($settlement, $remaining);
            }
        }
    }

    // If $another is a single space, answers whether $space is adjacent to it.
    // If $another is an array, answers whether $space is adjacent to any space in the array.
    public function isAdjacent($space, $another)
    {
        $adjacents = array_values($this->getAdjacentsOnTop($space));
        if (!is_array($another)) {
            $another = array($another);
        }
        foreach ($another as $test) {
            foreach ($adjacents as $adj) {
                if ($adj->exists() && $adj->x == $test->x && $adj->y == $test->y) {
                    return true;
                }
            }
        }
        return false;
    }

    public function allAdjacents($settlement)
    {
        $all = array();
        foreach ($settlement as $space) {
            $all += array_values($this->getAdjacentsOnTop($space));
        }
        return array_unique($all);
    }

    // Answers whether [x,y] coordinates for settlement spaces are contained in this array
    // With CONTAINS_ALL, returns true if all settlement spaces are in the container
    // With CONTAINS_ANY, returns true if one or more settlement spaces is in the container
    public function containsXY($container, $settlement, $mode)
    {
        // Empty always false
        if (empty($settlement) || empty($container)) {
            return false;
        }

        // The settlement cannot be contained if it is larger
        if ($mode == CONTAINS_ALL && count($settlement) > count($container)) {
            return false;
        }

        // Build [x,y] coordinates for container and settlement
        $xy = function ($space) {
            return $space->toXY();
        };
        $containerXY = array_map($xy, $container);
        $settlementXY = array_map($xy, $settlement);

        switch ($mode) {
            case CONTAINS_ALL:
                // Compute difference
                // (are ALL settlement spaces in the container?)
                $uncontained = array_diff($settlementXY, $containerXY);
                return empty($uncontained);

            case CONTAINS_ANY:
                // Compute intersection
                // (is ANY settlement space in the container?)
                $contained = array_intersect($settlementXY, $containerXY);
                return !empty($contained);
        }
    }

    public function hasBuilding($building, $spaces)
    {
        foreach ($spaces as $space) {
            if ($space->bldg_type == $building) {
                return true;
            }
        }
        return false;
    }

    public function isConnectedToBoard($space)
    {
        $adjacents = array_values($this->getSpaceAdjacents($space));
        foreach ($adjacents as $adj) {
            if ($adj->exists()) {
                return true;
            }
        }
        return false;
    }

    public function isValidTilePlacement($spaces)
    {
        list($space0, $space1, $space2) = $spaces;

        // First tile placement is always allowed
        if ($this->empty()) {
            return true;
        }

        // All spaces must be on the same level
        if ($space0->z != $space1->z || $space0->z != $space2->z) {
            return false;
        }

        // All spaces must be empty on the board
        if ($space0->exists() || $space1->exists() || $space2->exists()) {
            return false;
        }

        if ($space0->z > 1) {
            // Volcano must be above volcano of different rotation
            $below0 = $this->getSpaceBelow($space0);
            if ($below0->face !== VOLCANO || $below0->r === $space0->r) {
                return false;
            }

            // Other spaces must be supported
            $below1 = $this->getSpaceBelow($space1);
            $below2 = $this->getSpaceBelow($space2);
            if (!$below1->exists() || !$below2->exists()) {
                return false;
            }

            // Cannot destroy temple or tower
            if ($below1->bldg_type > HUT || $below2->bldg_type > HUT) {
                return false;
            }

            // Cannot destroy entire settlement
            $settlements = $this->getSettlements();
            $container = array($space1, $space2);
            foreach ($settlements as $player_id => $pSettlements) {
                foreach ($pSettlements as $settlement) {
                    if ($this->containsXY($container, $settlement, CONTAINS_ALL)) {
                        return false;
                    }
                }
            }
        } else {
            // One space must be adjacent to rest of the board
            $connected0 = $this->isConnectedToBoard($space0);
            $connected1 = $this->isConnectedToBoard($space1);
            $connected2 = $this->isConnectedToBoard($space2);
            if (!$connected0 && !$connected1 && !$connected2) {
                return false;
            }
        }

        return true;
    }

    public function getBuildingOptions($space, $player)
    {
        /// THERE ARE 4 BUILDING OPTIONS
        //  A - Single hut on level 1 tiles not connected to existing settlement
        //  B - temple on Settlements with at least other 3 buildings and no other temple
        //  C - Extend a settlement to all spaces of the same terrain connected to a settlement
        //  D - Tower on level 3 tiles with no other tower in the settlement

        $options = array();

        // Nothing to do if we cannot build here :-)
        if (!$space->canBuild()) {
            return $options;
        }

        // Calculate this player's adjacent settlements
        $adjacentSettlements = array();
        $settlements = $this->getSettlements();
        if (array_key_exists($player['id'], $settlements)) {
            $ownSettlements = $settlements[$player['id']];
            foreach ($ownSettlements as $settlement) {
                if ($this->isAdjacent($space, $settlement)) {
                    $adjacentSettlements[] = $settlement;
                }
            }
        }

        if (!empty($adjacentSettlements)) {
            $hutOptions = array();
            foreach ($adjacentSettlements as $settlement) {
                // OPTION C -- extend huts
                if ($player['huts'] > 0) {
                    $huts = array();
                    foreach ($settlement as $sSpace) {
                        $adjacents = $this->getAdjacentsOnTop($sSpace);
                        foreach ($adjacents as $adj) {
                            if ($adj->face == $space->face && !$adj->bldg_type) {
                                $huts["$adj"] = $adj;
                            }
                        }
                    }
                    $count = array_reduce($huts, function ($sum, $adj) {
                        return $sum + $adj->z;
                    });
                    if ($count > 0 && $player['huts'] >= $count) {
                        sort($huts, SORT_STRING);
                        $hutOptions[] = $huts;
                    }
                }

                // OPTION B -- temple
                if ($player['temples'] > 0 && count($settlement) >= 3 && !$this->hasBuilding(TEMPLE, $settlement)) {
                    $options[TEMPLE * 10] = array($space);
                }

                // OPTION D -- tower
                if ($player['towers'] > 0 && $space->z >= 3 && !$this->hasBuilding(TOWER, $settlement)) {
                    $options[TOWER * 10] = array($space);
                }
            }
            $hutOptions = array_unique($hutOptions, SORT_REGULAR);
            $i = 0;
            foreach ($hutOptions as $huts) {
                $options[HUT * 10 + $i++] = $huts;
            }
        } elseif ($player['huts'] > 0 && $space->z == 1) {
            // OPTION A -- new hut
            $options[HUT * 10] = array($space);
        }
        return $options;
    }
}
