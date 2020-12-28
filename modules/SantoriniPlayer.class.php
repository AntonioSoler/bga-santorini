<?php

// Cannot use Player, already taken by BGA
class SantoriniPlayer extends APP_GameClass
{
    public function __construct($game, $row)
    {
        $this->game = $game;
        $this->id = (int) $row['id'];
        $this->no = (int) $row['no'];
        $this->name = $row['name'];
        $this->team = (int) $row['team'];
        $this->color = $row['color'];
        $this->eliminated = $row['eliminated'] == 1;
        $this->zombie = $row['zombie'] == 1;

        // Load powers
        $powerIds = $this->game->powerManager->getPowerIdsInLocation('hand', $this->id);
        foreach ($powerIds as $powerId) {
            $this->powers[] = $this->game->powerManager->getPower($powerId, $this->id);
        }
    }

    private $game;
    private $id;
    private $no; // natural order
    private $name;
    private $team;
    private $color;
    private $eliminated = false;
    private $zombie = false;
    private $powers = [];

    public function getId()
    {
        return $this->id;
    }

    public function getNo()
    {
        return $this->no;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTeam()
    {
        return $this->team;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function isEliminated()
    {
        return $this->eliminated;
    }

    public function isZombie()
    {
        return $this->zombie;
    }

    public function getUiData()
    {
        return [
            'id'        => $this->id,
            'no'        => $this->no,
            'name'      => $this->name,
            'team'      => $this->team,
            'color'     => $this->color,
            'powers'    => array_map(function ($power) {
                return $power->getId();
            }, $this->powers)
        ];
    }

    /*
     * Powers
     */

    public function getPowers()
    {
        return $this->powers;
    }

    public function getPowerIds()
    {
        return Utils::getPowerIds($this->powers);
    }

    public function addPlayerPower($power)
    {
        $this->powers[] = $power;
        $power->setPlayerId($this->getId());
    }

    public function removePlayerPower($power)
    {
        $this->powers = array_filter($this->powers, function ($p) use ($power) {
            return $p->getId() != $power->getId();
        });
    }

    /*
     * Workers
     */

    public function addWorker($type, $location = 'desk')
    {
        $player_id = $this->id;
        $type_arg = $type . $this->team;
        self::DbQuery("INSERT INTO piece (`player_id`, `type`, `type_arg`, `location`) VALUES ('$player_id', 'worker', '$type_arg', '$location')");
        return self::DbGetLastId();
    }


    public function addToken($type, $type_arg = null, $location = 'hand', $visibility = VISIBLE_TO_ALL)
    {
        $player_id = $this->id;
        self::DbQuery("INSERT INTO piece (`player_id`, `type`, `type_arg`, `location`, `visibility`) VALUES ('$player_id', '$type', " . (is_null($type_arg) ? "NULL" : "'$type_arg'") . ", '$location', $visibility)");
        return self::DbGetLastId();
    }
}
