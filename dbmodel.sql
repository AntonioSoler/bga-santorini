
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- santorini implementation : © Emmanuel Colin <ecolin@boardgamearena.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `piece` (
   `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `card_type` varchar(16) NOT NULL,
   `card_type_arg` int(11) NOT NULL,
   `card_location` varchar(16) NOT NULL,
   `card_location_arg` int(11) NOT NULL,
   PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `board` (
   `space_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `x` int(2) NOT NULL,
   `y` int(2) NOT NULL,
   `z` int(2) NOT NULL,
   `piece_id` int(10) unsigned NULL,
   PRIMARY KEY (`space_id`),
   UNIQUE KEY `xyz` (`x`, `y`, `z`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
