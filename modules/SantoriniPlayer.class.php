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
        $cards = $this->game->cards->getCardsInLocation('hand', $this->id);
        foreach ($cards as $powerId => $card) {
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

    public function getPower($index = 0)
    {
        return array_key_exists($index, $this->powers) ? $this->powers[$index] : null;
    }

    public function getPowers()
    {
        return $this->powers;
    }

    public function addPower($powerId = null)
    {
        if (empty($powerId)) {
            // Draw the next card from the deck
            $powerId = ($this->game->cards->pickCard('deck', $this->id))['id'];
        } else {
            // Draw a specific card
            $card = $this->game->cards->getCard($powerId);
            if($card['location_arg'] == 1){
              $this->game->setGameStateValue('firstPlayer', $this->id);
            }
            $this->game->cards->moveCard($powerId, 'hand', $this->id);
        }
        $power = $this->game->powerManager->getPower($powerId, $this->id);
        $this->powers[] = $power;

        // Notify
        $this->game->notifyAllPlayers('powerAdded', clienttranslate('${player_name} receives ${power_name}, ${power_title}'), [
            'i18n' => ['power_name', 'power_title'],
            'player_id' => $this->getId(),
            'player_name' => $this->getName(),
            'power_id' => $power->getId(),
            'power_name' => $power->getName(),
            'power_title' => $power->getTitle(),
        ]);

        return $power;
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
