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
        $cards = $this->game->powerManager->cards->getCardsInLocation('hand', $this->id);
        foreach ($cards as $powerId => $card) {
            $this->powers[] = $this->game->powerManager->getPower($card['type'], $this->id);
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

    public function getPower($index = 0)
    {
        return array_key_exists($index, $this->powers) ? $this->powers[$index] : null;
    }

    public function getPowers()
    {
        return $this->powers;
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
    }
}
