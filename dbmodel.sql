
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- santorini implementation : © quietmint
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `tile` (
   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `card_type` varchar(16) NOT NULL,
   `card_type_arg` int(11) NOT NULL,
   `card_location` varchar(16) NOT NULL,
   `card_location_arg` int(11) NOT NULL,
   PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `board` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `x` int(2) NOT NULL,
   `y` int(3) NOT NULL,
   `z` int(2) NOT NULL,
   `r` int(3) NOT NULL,
   `face` int(1) NOT NULL,
   `tile_id` int(10) unsigned NOT NULL,
   `subface` int(1) NOT NULL,
   `tile_player_id` int(10) unsigned NOT NULL,
   `bldg_player_id` int(10) unsigned,
   `bldg_type` int(1),
   PRIMARY KEY (`id`),
   UNIQUE KEY `xyz` (`x`, `y`, `z`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `player` ADD `temples` INT NOT NULL DEFAULT 3;
ALTER TABLE `player` ADD `towers` INT NOT NULL DEFAULT 2;
ALTER TABLE `player` ADD `huts` INT NOT NULL DEFAULT 20;
