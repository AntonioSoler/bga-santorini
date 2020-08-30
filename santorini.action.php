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
    $this->game->checkAction('addOffer');
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->addOffer($powerId);
    self::ajaxResponse();
  }

  public function removeOffer()
  {
    self::setAjaxMode();
    $this->game->checkAction('removeOffer');
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->removeOffer($powerId);
    self::ajaxResponse();
  }

  public function confirmOffer()
  {
    self::setAjaxMode();
    $this->game->checkAction('confirmOffer');
    $this->game->confirmOffer();
    self::ajaxResponse();
  }

  public function chooseFirstPlayer()
  {
    $this->game->checkAction('chooseFirstPlayer');
    self::setAjaxMode();
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->chooseFirstPlayer($powerId);
    self::ajaxResponse();
  }

  public function choosePower()
  {
    $this->game->checkAction('choosePower');
    self::setAjaxMode();
    $powerId = (int) self::getArg('powerId', AT_int, true);
    $this->game->choosePower($powerId);
    self::ajaxResponse();
  }

  /*
   * TODO
   */
  public function skipPower()
  {
    self::setAjaxMode();
    $this->game->checkAction('skip');
    $this->game->skipPower();
    self::ajaxResponse();
  }

  public function usePowerWork()
  {
    self::setAjaxMode();
    $this->game->checkAction('use');
    $powerId = (int) self::getArg('powerId', AT_posint, true);
    $workerId = (int) self::getArg('workerId', AT_posint, true);
    $x = (int) self::getArg('x', AT_int, true);
    $y = (int) self::getArg('y', AT_int, true);
    $z = (int) self::getArg('z', AT_posint, true);
    $arg = self::getArg('arg', AT_int, false, null);
    if ($arg != null) {
      $arg = (int) $arg;
    }
    $this->game->usePowerWork($powerId, $workerId, $x, $y, $z, $arg);
    self::ajaxResponse();
  }



  /*
   * TODO
   */
  public function placeWorker()
  {
    self::setAjaxMode();
    $this->game->checkAction('placeWorker');
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
    if ($arg != null) {
      $arg = (int) $arg;
    }
    $this->game->work($workerId, $x, $y, $z, $arg);
    self::ajaxResponse();
  }

  /*
   * TODO
   */
  public function skipWork()
  {
    self::setAjaxMode();
    $this->game->checkAction('skip');
    $this->game->skipWork();
    self::ajaxResponse();
  }


  public function resign()
  {
    self::setAjaxMode();
    $this->game->checkAction('resign');
    $this->game->resign();
    self::ajaxResponse();
  }

  /*
   * TODO
   */
  public function cancelPreviousWorks()
  {
    self::setAjaxMode();
    $this->game->checkAction('cancel');
    $this->game->cancelPreviousWorks();
    self::ajaxResponse();
  }


  public function confirmTurn()
  {
    self::setAjaxMode();
    $this->game->checkAction('confirm');
    $this->game->confirmTurn();
    self::ajaxResponse();
  }

  public function loadBugSQL()
  {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    echo '{"status":1,"data":{"valid":1,"data":null}}';
  }
}
