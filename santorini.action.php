<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * santorini implementation : © Emmanuel Colin <ecolin@boardgamearena.com>
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

  public function addOffer()
  {
    self::setAjaxMode();
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->addOffer($powerId);
    self::ajaxResponse();
  }

  public function removeOffer()
  {
    self::setAjaxMode();
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->removeOffer($powerId);
    self::ajaxResponse();
  }

  public function confirmOffer()
  {
    self::setAjaxMode();
    $this->game->confirmOffer();
    self::ajaxResponse();
  }

  public function choosePower()
  {
    self::setAjaxMode();
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->checkAction('choosePower');
    $this->game->choosePower($powerId);
    self::ajaxResponse();
  }


  /*
   * TODO
   */
  public function placeWorker()
  {
    self::setAjaxMode();
    $workerId = (int) self::getArg('workerId', AT_int, true);
    $x = (int) self::getArg('x', AT_int, true);
    $y = (int) self::getArg('y', AT_int, true);
    $z = (int) self::getArg('z', AT_posint, true);
    $this->game->placeWorker($workerId, $x, $y, $z);
    self::ajaxResponse();
  }

  /*
   * TODO
   */
  public function work()
  {
    self::setAjaxMode();
    $workerId = (int) self::getArg('workerId', AT_posint, true);
    $x = (int) self::getArg('x', AT_int, true);
    $y = (int) self::getArg('y', AT_int, true);
    $z = (int) self::getArg('z', AT_posint, true);
    $arg = self::getArg('arg', AT_int, false, null);
    $this->game->work($workerId, $x, $y, $z, $arg);
    self::ajaxResponse();
  }

  /*
   * TODO
   */
  public function skipWork()
  {
    self::setAjaxMode();
    $this->game->skipWork();
    self::ajaxResponse();
  }
}
