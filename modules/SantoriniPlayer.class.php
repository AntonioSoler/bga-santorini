<?php

// Cannot use Player, already taken by BGA
class SantoriniPlayer extends APP_GameClass
{
    /* Returns the SantoriniPlayer object for the given player ID */
    public static function getPlayer($game, $id)
    {
        $players = SantoriniPlayer::getPlayers($game, [$id]);
        return $players[0];
    }

    /* Returns array of SantoriniPlayer objects for all/specified player IDs */
    public static function getPlayers($game, $ids = null)
    {
        $players = [];
        $sql = "SELECT player_id id, player_color color, player_name name, player_score score, player_zombie zombie, player_eliminated eliminated, player_team team, player_no no FROM player";
        if (!empty($ids)) {
            $sql .= " WHERE player_id IN (" . implode(',', $ids) . ")";
        }
        $rows = self::getObjectListFromDb($sql);
        foreach ($rows as $row) {
            $player = new SantoriniPlayer($game, $row['id']);
            $player->no = (int) $row['no'];
            $player->name = $row['name'];
            $player->team = (int) $row['team'];
            $player->color = $row['color'];
            $player->eliminated = $row['eliminated'] == 1;
            $player->zombie = $row['zombie'] == 1;

            // Load powers
            $cards = $game->cards->getCardsInLocation('hand', $player->id);
            foreach ($cards as $powerId => $card) {
                $player->powers[] = Power::getPower($game, $powerId);
            }
            $players[] = $player;
        }
        return $players;
    }

    public function __construct($game, $id)
    {
        $this->game = $game;
        $this->id = (int) $id;
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
            $this->game->cards->moveCard($powerId, 'hand', $this->id);
        }
        $power = Power::getPower($this->game, $powerId);
        $this->powers[] = $power;

        // Send notification
        $args = array(
            'i18n' => array('power_name', 'power_title'),
            'player_id' => $this->getId(),
            'player_name' => $this->getName(),
            'power_id' => $power->getId(),
            'power_name' => $power->getName(),
            'power_title' => $power->getTitle(),
        );
        $this->game->notifyAllPlayers('powerAdded', clienttranslate('${player_name} receives ${power_name}, ${power_title}'), $args);

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
