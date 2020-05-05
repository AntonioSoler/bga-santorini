
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- santorini implementation : © Emmanuel Colin <ecolin@boardgamearena.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `piece` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `player_id` int(11),
   `type` varchar(16) NOT NULL,
   `type_arg` varchar(16),
   `location` varchar(16) NOT NULL,
   `x` int(2),
   `y` int(2),
   `z` int(2),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `player` ADD `player_team` INT(1) UNSIGNED NOT NULL;
ALTER TABLE `player` ADD `player_god` INT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `player` ADD `player_hero` INT(1) UNSIGNED NOT NULL DEFAULT '0';