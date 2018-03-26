<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * santorini implementation : © quietmint
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * santorini.action.php
 *
 * santorini main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/santorini/santorini/myAction.html", ...)
 *
 */


class action_santorini extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default()
    {
        if (self::isArg('notifwindow')) {
            $this->view = 'common_notifwindow';
            $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
        } else {
            $this->view = 'santorini_santorini';
            self::trace('Complete reinitialization of board game');
        }
    }

    public function commitTile()
    {
        self::setAjaxMode();
        $x = (int) self::getArg('x', AT_int, true);
        $y = (int) self::getArg('y', AT_int, true);
        $z = (int) self::getArg('z', AT_posint, true);
        $r = (int) self::getArg('r', AT_posint, true);
        $this->game->actionCommitTile($x, $y, $z, $r);
        self::ajaxResponse();
    }

	public function selectSpace()
    {
        self::setAjaxMode();
        $x = (int) self::getArg('x', AT_int, true);
        $y = (int) self::getArg('y', AT_int, true);
        $z = (int) self::getArg('z', AT_posint, true);
        $this->game->actionSelectSpace($x, $y, $z);
        self::ajaxResponse();
    }

	public function cancel()
    {
        self::setAjaxMode();
        $this->game->actionCancel();
        self::ajaxResponse();
    }

    public function commitBuilding()
    {
        self::setAjaxMode();
        $x = (int) self::getArg('x', AT_int, true);
        $y = (int) self::getArg('y', AT_int, true);
        $z = (int) self::getArg('z', AT_posint, true);
        $option_nbr = (int) self::getArg('option_nbr', AT_posint, true);
        $this->game->actionCommitBuilding($x, $y, $z, $option_nbr);
        self::ajaxResponse();
    }
}
