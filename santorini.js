"use strict";

/**
	*------
	* BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
	* santorini implementation : (c) Tisaac & Quietmint & Morgalad
	*
	* This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
	* See http://en.boardgamearena.com/#!doc/Studio for more information.
	* -----
	*
	* santorini.js
	*
	* santorini user interface script
	*
	* In this file, you are describing the logic of your user interface, in Javascript language.
	*
	*/
//# sourceURL=santorini.js
//@ sourceURL=santorini.js

const HELPERS = 100;
const HELPERS_ENABLED = 1;
const HELPERS_DISABLED = 1;
const CONFIRM = 101;
const CONFIRM_TIMER = 1;
const CONFIRM_ENABLED = 2;
const CONFIRM_DISABLED = 3;

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

function isBrowserSupported() {
  // Block legacy browsers that can't load the board (anything pre-2016)
  var ok = typeof Board != 'undefined';
  // Also block Safari 10 for now (theoretically *should* work, but doesn't)
  if ((navigator.userAgent.indexOf('Safari/602.') > -1 || navigator.userAgent.indexOf('Safari/603.') > -1)
    && navigator.userAgent.indexOf('Chrome') == -1
    && navigator.userAgent.indexOf('Chromium') == -1
  ) {
    ok = false;
  }
  debug('Browser supported:', ok);
  return ok;
}

function isWebGLAvailable() {
  var ok = false;
  try {
    var canvas = document.createElement('canvas');
    ok = !!(window.WebGLRenderingContext && (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')))
      || !!(window.WebGL2RenderingContext && canvas.getContext('webgl2'));
  } catch (e) {
  }
  debug('WebGL available:', ok);
  return ok;
}

define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter"], function (dojo, declare) {

  // Dojo ShrinkSafe does not support named function expressions
  // If you need to use this.inherited(), define the function here (not inside "return")

  function override_setLoader(value, max) {
    // [Undocumented] Called by BGA framework when loading progress changes
    // Call our onLoadComplete() when fully loaded
    this.inherited(override_setLoader, arguments);
    if (!this.isLoadingComplete && value >= 100) {
      this.isLoadingComplete = true;
      this.onLoadingComplete();
    }
  }

  function override_adaptStatusBar() {
    // [Undocumented] Called by BGA framework on scroll
    // Handle "position: fixed" for power detail
    this.inherited(override_adaptStatusBar, arguments);
    if (this.gamedatas.gamestate.name == 'buildOffer' || this.gamedatas.gamestate.name == 'chooseNyxNightPower') {
      var nodes = dojo.query('#grid-detail .power-detail');
      if (nodes.length > 0) {
        var style = { position: '', top: '' };
        if (dojo.hasClass("page-title", "fixed-page-title")) {
          var height = $('page-title').getBoundingClientRect().height + 6;
          style = { position: 'fixed', top: height + 'px' };
        }
        dojo.style(nodes[0], style);
      }
    }
  }

  var dockedlog_to_move_id = {};
  function override_onPlaceLogOnChannel(msg) {
    // [Undocumented] Called by BGA framework on any notification message
    // Handle cancelling log messages for restart turn
    var currentLogId = this.next_log_id;
    this.inherited(override_onPlaceLogOnChannel, arguments);
    if (msg.move_id && this.next_log_id != currentLogId) {
      var moveId = +msg.move_id;
      dockedlog_to_move_id[currentLogId] = moveId;
      if (this.gamedatas.cancelMoveIds != null && this.gamedatas.cancelMoveIds.includes(moveId)) {
        this.cancelLogs([moveId]);
      }
    }
  }

  var queuedFunction = null;
  function override_unlockInterface(uid) {
    // [Undocumented] Called by BGA framework after interface is unlocked following ajax call
    // Handle auto-confirm
    this.inherited(override_unlockInterface, arguments);
    if (queuedFunction != null) {
      var fn = queuedFunction;
      queuedFunction = null; // clear first to avoid infinite loop
      fn();
    }
  }

  return declare("bgagame.santorini", ebg.core.gamegui, {
    /*
     * [Undocumented] Override BGA framework functions
     */
    setLoader: override_setLoader,
    adaptStatusBar: override_adaptStatusBar,
    onPlaceLogOnChannel: override_onPlaceLogOnChannel,
    unlockInterface: override_unlockInterface,

    /*
     * Constructor
     */
    constructor: function () {
      // Fix mobile viewport (remove CSS zoom)
      this.default_viewport = 'width=550, user-scalable=no';
      this._focusedContainer = null;
    },

    /*
     * Setup:
     *  This method set up the game user interface according to current game situation specified in parameters
     *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
     *
     * Params :
     *  - mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
     */
    setup: function (gamedatas) {
      var _this = this;
      debug('SETUP', gamedatas);

      // Check for supported browser
      if (!isBrowserSupported()) {
        this.showFatalError('browser');
        return;
      }

      // Check for WebGL
      if (!isWebGLAvailable()) {
        this.showFatalError('webgl');
        return;
      }

      // Setup powers
      Object.values(gamedatas.powers)
        .sort(function (power1, power2) {
          return _(power1.name).localeCompare(_(power2.name))
        }).forEach(function (power, index) {
          // For JS code: Keep English name, add sort order
          power.sort = index;
          power.nameEnglish = power.name.split(" ")[0];

          // For HTML template: Translate text, add counter
          power.name = _(power.name);
          power.title = _(power.title);
          power.text = '<p>' + power.text.map(function (text) {
            return _(text).replace(/\[/g, '<b>').replace(/\]/g, '</b>');
          }).join('</p>\n<p>') + '</p>';
          power.type = power.hero ? 'hero' : '';
          power.counter = power.counter || 0;
          power.playerCount = power.playerCount.join(', ');
          power.tooltipGolden = power.golden ? _('Golden Fleece') : '';
          power.tooltipPlayerCount = _('Supported player count');
        });
      gamedatas.fplayers.forEach(function (player) {
        dojo.place(_this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
        player.powers.forEach(function (powerId) {
          _this.addPower(player.id, powerId, 'init', false);
        });
      });

      // Setup the board (3d scene using threejs)
      this.board = new Board($('scene-container'), URL);
      this.setupPreference();
      this.setupWikiText();

      // Setup workers and buildings
      gamedatas.placedPieces.forEach(function (piece) {
        _this.board.addPiece(piece);
      });
      if (gamedatas.goldenFleece) {
        this.addGoldenFleece(gamedatas.goldenFleece);
      } else if (gamedatas.nyxNightPower) {
        this.addNyxNightPower(gamedatas.nyxNightPower);
      }
      // Setup game notifications
      this.setupNotifications();
    },

    showFatalError: function (err) {
      var href = err == 'webgl' ? 'https://get.webgl.org/' : 'https://browsehappy.com/';
      var msg = err == 'webgl' ? _('Your browser or graphics card does not support WebGL') : _('Your outdated browser is not supported');
      dojo.style('browser-error', 'display', 'block');
      dojo.attr('browser-error', 'href', href);
      $('browser-error').innerHTML = '<img src="https://noto-website-2.storage.googleapis.com/emoji/emoji_u1f627.png" alt="Anguished Face">'
        + '<div>' + msg + '</div>'
        + '<div class="ua" title="User-Agent">' + navigator.userAgent + '</div>'
        + '<div id="errorAbandon" class="bgabutton bgabutton_blue" onclick="$(\'ingame_menu_abandon\').click();return false">' + _('Abandon the game (no penalty)') + '</div>';
    },

    onLoadingComplete: function () {
      debug('Loading complete');
      // Handle previously cancelled moves
      this.cancelLogs(this.gamedatas.cancelMoveIds);

      if (!this.board) {
        return;
      }

      this.onScreenWidthChange();
      this.focusContainer();
      if (this._focusedContainer != 'scene-container') {
        this.board.onLoad();
      }
    },

    // Returns true for spectators, instant replay (during game), archive mode (after game end)
    isReadOnly: function () {
      return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode;
    },

    comparePowersByName: function (power1, power2) {
      return power1.sort - power2.sort;
    },

    comparePowerIdsByName: function (id1, id2) {
      return gameui.gamedatas.powers[id1].sort - gameui.gamedatas.powers[id2].sort;
    },

    comparePowerCardsByName: function (card1, card2) {
      return gameui.gamedatas.powers[card1.id].sort - gameui.gamedatas.powers[card2.id].sort;
    },

    setupPreference: function () {
      var _this = this;
      var updatePreference = function (e) {
        var match = e.target.id.match(/^preference_[cf]ontrol_(\d+)$/)
        if (!match) {
          return;
        }
        var pref = +match[1];
        var prefValue = +e.target.value;
        debug('Update preference', pref + ' = ' + prefValue);
        _this.prefs[pref].value = prefValue;
        if (pref == HELPERS) {
          _this.board.toggleCoordsHelpers(prefValue == HELPERS_ENABLED);
        }
      };

      dojo.query('.preference_control').connect('onchange', updatePreference);
      updatePreference({ target: $('preference_control_' + HELPERS) });

      // Add reset camera button
      var resetCameraBlock = this.format_block('jstpl_resetCamera', {
        camera: _('Camera'),
        reset: __('lang_mainsite', 'Reset to default'),
      });
      var q = dojo.query('#ingame_menu_content .preference_choice');
      if (q.length > 0) {
        dojo.place(resetCameraBlock, q[q.length - 1], 'after');
      }
      q = dojo.query('#pagesection_options .preference_choice');
      if (q.length > 0) {
        dojo.place(resetCameraBlock, q[q.length - 1], 'after');
      }
      dojo.query('.buttonResetCamera').connect('onclick', function () {
        $('page-title').scrollIntoView({ block: "start", inline: "start" });
        $('scene-container').scrollIntoView({ block: "end", inline: "start" });
        _this.board.updateSize();
        _this.board.resetCameraPosition();
      });
    },

    setupWikiText: function () {
      var query = dojo.query('.debug_section');
      if (!isDebug || query.length == 0) {
        return;
      }

      var _this = this;
      var button = dojo.place('<div id="wikiTextButton" class="bgabutton bgabutton_blue">Generate wiki power list</div>', query[0]);
      dojo.connect(button, 'onclick', function () {
        var powers = Object.values(_this.gamedatas.powers).sort(function (power1, power2) {
          return power1.sort - power2.sort;
        });
        var txt = "\n<!--\n\n\n\n    Please do not modify anything below this point.\n    Content generated directly from the game code.\n\n\n\n-->\n"
          + "== List of Powers (" + powers.length + ") ==\n"
          + powers.map(function (p) {
            var info = ['Players: ' + p.playerCount];
            var color = '';
            if (p.id <= 10) {
              info.push('Simple God');
            } else if (p.hero) {
              info.push('Hero Power');
              color = 'color:#6a1b9a';
            }
            if (p.golden) {
              info.push('Golden Fleece Variant');
            }
            if (p.text.includes('REVISED POWER')) {
              info.push('REVISED POWER');
            }
            var txt = p.text.replace('<p><b>REVISED POWER</b></p>', '').replace(/<p>/g, '<p style="padding-left: 2em;' + color + '">');
            return '<p style="' + color + '"><b>' + p.name + '</b>, <i>' + p.title + '</i><br>' + '<small>' + info.join(' &mdash; ') + '</small></p>\n' + txt;
          }).join('\n----\n');
        console.log(txt);
        navigator.clipboard.writeText(txt);
        _this.showMessage('Text copied to clipboard &mdash; <a href="https://en.doc.boardgamearena.com/index.php?title=Gamehelpsantor' + 'ini&action=edit" target="_blank">edit Gamehelpsantor' + 'ini</a>', 'info');
      });
    },

    onScreenWidthChange: function () {
      dojo.style('page-content', 'zoom', '');
      dojo.style('page-title', 'zoom', '');
      dojo.style('right-side-first-part', 'zoom', '');
      if (!this.board) { return; }
      this.board.updateSize();
    },

		/*
		 * notif_cancel:
		 *   called whenever a player restart their turn
		 */
    notif_cancel: function (n) {
      debug('Notif: cancel turn', n.args);
      this.board.diff(n.args.placedPieces);
      this.cancelLogs(n.args.moveIds);
    },

    /*
     * cancelLogs:
     *   strikes all log messages related to the given array of move IDs
     */
    cancelLogs: function (moveIds) {
      if (Array.isArray(moveIds)) {
        debug('Cancel log messages for move IDs', moveIds);
        var elements = [];
        // Desktop logs
        for (var logId in this.log_to_move_id) {
          var moveId = +this.log_to_move_id[logId];
          if (moveIds.includes(moveId)) {
            elements.push($('log_' + logId));
          }
        }
        // Mobile logs
        for (var logId in dockedlog_to_move_id) {
          var moveId = +dockedlog_to_move_id[logId];
          if (moveIds.includes(moveId)) {
            elements.push($('dockedlog_' + logId));
          }
        }
        // Add strikethrough
        elements.forEach(function (e) {
          if (e != null) {
            dojo.addClass(e, 'cancel');
          }
        });
      }
    },

    /*
     * addPower:
     * 	add a power card to given player
     */
    addPower: function (playerId, powerId, reason, showDialog) {
      var circeQuery = null;
      if (reason == 'circe') {
        // Use another player's power if Circe is stealing it
        circeQuery = dojo.query('.mini-card.power-' + powerId);
      }
      // Create mini card
      var night = (reason == 'nyxNight' || (reason == 'init' && powerId == this.gamedatas.nyxNightPower)) ? 'night' : '';
      var powerDetail = this.createPowerDetail(powerId, night);
      var card = this.createMiniCard(playerId, powerId, night);
      card.id = "mini-card-" + playerId + "-" + powerId;
      this.addTooltipHtml(card.id, powerDetail);

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialog-' + playerId + "-" + powerId);
      powerDialog.setTitle(playerId == this.player_id ? _("Your power") : _("Opponent's power"));
      powerDialog.setContent(powerDetail);
      powerDialog.replaceCloseCallback(function () { powerDialog.hide(); });
      dojo.connect(card, "onclick", function (ev) { powerDialog.show(); });
      if (showDialog && playerId == this.player_id) {
        powerDialog.show();
      }

      // During first page load, no animation is needed
      if (reason == 'init') {
        return;
      }

      // Make the mini-card invisible to prep for animation
      dojo.style(card, 'visibility', 'hidden');

      if (reason == 'setup') {
        // Use the large card as the dummy, if it exists
        dummy = $('power-choose-' + powerId);
      } else if (circeQuery != null && circeQuery.length > 0) {
        dummy = circeQuery[0];
        dojo.style(dummy, 'z-index', '1');
      }

      if (dummy == null) {
        // Create a dummy mini card for the animation
        var dummy = this.createMiniCard(playerId, powerId, night);
        dummy.id = 'miniCard-dummy';
        dojo.style(dummy, 'position', 'absolute');
        var animationTarget = 'topbar';
        if (reason == 'ram' || reason == 'nyxNight') {
          animationTarget = 'power-' + reason;
        }
        this.placeOnObject(dummy, animationTarget);
      }

      // Slide the dummy to the position of the real card
      this.slide(dummy, card).then(function () {
        // Delete the dummy and show the real card
        dojo.style(card, 'visibility', '');
        dojo.destroy(dummy);
      });
    },

    removePower: function (playerId, powerId, reason) {
      var card = $("mini-card-" + playerId + "-" + powerId);
      var animationTarget = 'topbar';
      if (reason == 'ram' || reason == 'nyxNight') {
        animationTarget = 'power-' + reason;
      }
      this.slideToObjectAndDestroy(card, animationTarget);
    },

		/*
		 * notif_updatePowerUI:
		 *   called whenever a power UI is updated (eg Morpheus)
		 */
    notif_updatePowerUI: function (n) {
      debug('Notif: updating power UI', n.args);
      this.gamedatas.powers[n.args.powerId].counter = n.args.counter;
      var q = dojo.query('.mini-card.power-' + n.args.powerId + ' .power-counter');
      if (q.length > 0) {
        q[0].textContent = n.args.counter;
        // Restart the CSS animation
        q[0].style.animation = 'none';
        q[0].offsetWidth; // force repaint
        q[0].style.animation = '';
      }
    },

		/*
		 * TODO description
		 */
    takeAction: function (action, data, callback) {
      data = data || {};
      data.lock = true;
      callback = callback || function (res) { };
      this.stopActionTimer();
      debug('Taking action: ' + action, data);
      this.ajaxcall("/santorini/santorini/" + action + ".html", data, this, callback);
    },


    ///////////////////////////////////////
    ////////  Game & client states ////////
    ///////////////////////////////////////

    /*
     * onEnteringState:
     * 	this method is called each time we are entering into a new game state.
     *
     * params:
     *  - str stateName : name of the state we are entering
     *  - mixed args : additional information
     */
    onEnteringState: function (stateName, args) {
      debug('Entering state: ' + stateName, args);

      // Update gamestate description when skippable
      if (args && args.args && args.args.skippable && this.gamedatas.gamestate.descriptionskippable) {
        this.gamedatas.gamestate.description = this.gamedatas.gamestate.descriptionskippable;
        this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate.descriptionmyturnskippable;
        this.updatePageTitle();
      }

      // Stop if no board
      if (!this.board) { return; }
      this.focusContainer();

      // Stop here if it's not the current player's turn for some states
      if (["playerUsePower", "playerPlaceWorker", "playerPlaceRam", "playerMove", "playerBuild", "confirmTurn", "gameEnd"].includes(stateName)) {
        if (!this.isCurrentPlayerActive()) {
          return;
        }
      }

      // Call appropriate method
      var methodName = "onEnteringState" + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) {
        this[methodName](args.args);
      }
    },


    /*
     * onLeavingState:
     * 	this method is called each time we are leaving a game state.
     *
     * params:
     *  - str stateName : name of the state we are leaving
     */
    onLeavingState: function (stateName) {
      debug('Leaving state: ' + stateName);
      if (!this.board) { return; }
      this.clearPossible();
    },

    /*
     * onUpdateActionButtons:
     * 	called by BGA framework before onEnteringState
     *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
     */
    onUpdateActionButtons: function (stateName, args, suppressTimers) {
      debug('Update action buttons: ' + stateName, args); // Make sure it the player's turn
      this.stopActionTimer();

      if (!this.isCurrentPlayerActive()) {
        return;
      }

      if (stateName == "playerMove" || stateName == "playerBuild" || stateName == "playerUsePower") {
        this.addActionButton('buttonResign', _('Resign'), 'onClickResign', null, false, 'red');
        if (args.cancelable) {
          this.addActionButton('buttonCancel', _('Restart turn'), 'onClickCancel', null, false, 'gray');
        }
        if (args.skippable) {
          this.addActionButton('buttonSkip', _('Skip'), 'onClickSkip', null, false, 'gray');
        }
      }

      if (stateName == "confirmTurn") {
        this.addActionButton('buttonConfirm', _('Confirm'), 'onClickConfirm', null, false, 'blue');
        this.addActionButton('buttonCancel', _('Restart turn'), 'onClickCancel', null, false, 'gray');
        if (!suppressTimers) {
          this.startActionTimer('buttonConfirm');
        }
      }
    },

    startActionTimer: function (buttonId) {
      var button = $(buttonId);
      var isReadOnly = this.isReadOnly();
      var prefValue = (this.prefs[CONFIRM] || {}).value;
      if (button == null || isReadOnly || prefValue == CONFIRM_ENABLED) {
        debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly, 'prefValue=' + prefValue);
        return;
      }

      if (prefValue == CONFIRM_DISABLED) {
        var fn = function () { button.click(); };
        if (this.isInterfaceLocked()) {
          queuedFunction = fn;
        } else {
          fn();
        }
        return;
      }

      var _this = this;
      this.actionTimerLabel = button.innerHTML;
      this.actionTimerSeconds = 15;
      this.actionTimerFunction = function () {
        var button = $(buttonId);
        if (button == null) {
          _this.stopActionTimer();
        } else if (_this.actionTimerSeconds-- > 1) {
          debug('Timer ' + buttonId + ' has ' + _this.actionTimerSeconds + ' seconds left');
          button.innerHTML = _this.actionTimerLabel + ' (' + _this.actionTimerSeconds + ')';
        } else {
          debug('Timer ' + buttonId + ' execute');
          button.click();
        }
      };
      this.actionTimerFunction();
      this.actionTimerId = window.setInterval(this.actionTimerFunction, 1000);
      debug('Timer #' + this.actionTimerId + ' ' + buttonId + ' start');
    },

    stopActionTimer: function () {
      if (this.actionTimerId != null) {
        debug('Timer #' + this.actionTimerId + ' stop');
        window.clearInterval(this.actionTimerId);
        delete this.actionTimerId;
      }
    },

    ///////////////////////////////////////
    ///////////////////////////////////////
    //////////    Fair division   /////////
    ///////////////////////////////////////
    ///////////////////////////////////////
    // As stated in the rulebook, the fair division process goes as follows :
    //  - the contestant pick n powers
    //  - each player choose one power (contestant is last to choose)
    //  - contestant choose the first player to place worker TODO
    //////////////////////////////////////


    /////////////////////
    //// Build Offer ////
    /////////////////////

    /*
     * BuildOffer: in the fair division setup,the contestant can select #players powers from available powers (depending on game option)
     */
    onEnteringStateBuildOffer: function (args) {
      var _this = this;

      // Display selected powers, sorted by name
      dojo.empty('cards-offer');
      args.offer.sort(this.comparePowerIdsByName);
      args.offer.forEach(function (powerId) {
        var div = dojo.place(_this.createPowerSmall(powerId), 'cards-offer');
        dojo.addClass(div, 'selected');
        dojo.connect(div, 'onclick', function (e) {
          return _this.onClickPowerSmall(powerId);
        });
      });

      this._nMissingPowers = args.count - args.offer.length;

      // Display remaining powers, sorted by name
      dojo.empty('cards-deck');
      args.deck.sort(this.comparePowerIdsByName);
      args.deck.forEach(function (powerId) {
        var div = dojo.place(_this.createPowerSmall(powerId), 'cards-deck');
        dojo.connect(div, 'onclick', function (e) {
          return _this.onClickPowerSmall(powerId);
        });
      });

      this.updateBannedPowers(args.banned);
      this.buildOfferActionButtons();
    },


    /*
     * buildOfferActionButtons: show confirm button if the count is correct
     */
    buildOfferActionButtons: function () {
      var _this = this;
      if (this.gamedatas.gamestate.name == 'buildOffer') {
        var count = this.gamedatas.gamestate.args.offer.length + this.gamedatas.gamestate.args.deck.length;
        var countOffer = dojo.query('#cards-offer .power-card').length;
        var countDeck = count - countOffer;
        $('title-offer').textContent = dojo.string.substitute(_('Powers On Offer (${count}):'), { count: countOffer });
        $('title-deck').textContent = dojo.string.substitute(_('Powers Available (${count}):'), { count: countDeck });
      }

      if (!this.isCurrentPlayerActive()) {
        return;
      }
      this.removeActionButtons();

      if (this.gamedatas.gamestate.name == 'buildOffer') {
        if (this._displayedPower) {
          var powerDiv = $('power-small-' + this._displayedPower),
            isBanned = dojo.hasClass(powerDiv, 'banned'),
            isSelected = dojo.hasClass(powerDiv, 'selected'),
            power = this.getPower(this._displayedPower);
          if (isSelected) {
            var buttonText = dojo.string.substitute(_('Remove ${name}'), power);
            this.addActionButton('buttonRemoveFromOffer', buttonText, this.removeOffer.bind(this), null, false, 'red');
          } else if (!isBanned) {
            var buttonText = dojo.string.substitute(_('Add ${name}'), power);
            this.addActionButton('buttonAddToOffer', buttonText, this.addOffer.bind(this), null, false, 'blue');
          }
        }
        // Enough powers : confirm button
        if (this._nMissingPowers == 0) {
          this.addActionButton('buttonConfirmOffer', _('Confirm'), 'onClickConfirmOffer', null, false, 'blue');
        }

      } else if (this.gamedatas.gamestate.name == 'chooseNyxNightPower') {
        if (this._displayedPower) {
          var power = this.getPower(this._displayedPower);
          this.addActionButton('buttonChooseNyxNightPower', _(power.name), function () {
            _this.takeAction('chooseNyxNightPower', { powerId: this._displayedPower });
          }, null, false, 'blue');
        }
      }
    },

    /*
     * updateBannedPowers: display only "selectable" powers
     */
    updateBannedPowers: function (bannedIds) {
      dojo.query(".power-card.small").removeClass("banned");
      bannedIds.forEach(function (powerId) {
        if ($("power-small-" + powerId)) {
          dojo.addClass("power-small-" + powerId, "banned");
        }
      });
    },

    /*
     * onClickPowerSmall:
     * 	 during fair division setup, when clicking on a small card while building the offer
     */
    onClickPowerSmall: function (powerId) {
      var powerDiv = $('power-small-' + powerId),
        isActive = this.isCurrentPlayerActive(),
        isBanned = dojo.hasClass(powerDiv, 'banned'),
        isDisplayed = dojo.hasClass(powerDiv, 'displayed'),
        isSelected = dojo.hasClass(powerDiv, 'selected'),
        isWait = dojo.hasClass(powerDiv, 'wait');
      // Everyone may view details on first click
      this._displayedPower = powerId;

      if (this.gamedatas.gamestate.name == 'chooseNyxNightPower' || !isDisplayed) {
        // Mark only this card as displayed
        dojo.query('.power-card.small.displayed').removeClass('displayed');
        dojo.addClass(powerDiv, 'displayed');
        dojo.place(this.createPowerDetail(powerId), 'grid-detail', 'only');
        this.adaptStatusBar();
      } else if (!isWait && isActive) {
        // Otherwise, active player may select/unselect the power
        // Already selected => unselect it
        if (isSelected) {
          this.removeOffer();
        } else if (!isBanned) {
          // Not yet select + still need powers => select it
          this.addOffer();
        }
      }

      this.buildOfferActionButtons();
    },

    addOffer: function () {
      dojo.addClass('power-small-' + this._displayedPower, 'wait');
      this.takeAction("addOffer", { powerId: this._displayedPower });
    },

    removeOffer: function () {
      dojo.addClass('power-small-' + this._displayedPower, 'wait');
      this.takeAction("removeOffer", { powerId: this._displayedPower });
    },


    /*
     * notif_addOffer:
     *   called during fair division setup, when player 1 adds a power to the offer
     */
    notif_addOffer: function (n) {
      var _this = this;
      debug('Notif: addOffer', n.args);

      // Create a dummy in the offer
      var dummy = this.createPowerSmall(0);
      // Find the right position
      var power = this.getPower(n.args.powerId);
      var nextPower = null;
      dojo.query('#cards-offer .power-card').some(function (div) {
        if (div.getAttribute('data-sort') > power.sort) {
          nextPower = div;
          return true;
        }
      });

      // Insert it
      if (nextPower != null) {
        dummy = dojo.place(dummy, nextPower, 'before');
      } else {
        dummy = dojo.place(dummy, 'cards-offer', 'last');
      }

      // Slide the real card to the position of the dummy
      var powerDivId = 'power-small-' + n.args.powerId;
      dummy.id = 'addOffer-dummy';
      this.slide(powerDivId, dummy.id).then(function () {
        // Replace the dummy with the real card
        var powerDiv = dojo.place(powerDivId, dummy.id, 'replace');
        dojo.style(powerDiv, { top: null, left: null });
        dojo.addClass(powerDiv, 'selected');
        dojo.removeClass(powerDiv, 'wait');
        _this._nMissingPowers--;

        _this.updateBannedPowers(n.args.banned);
        _this.buildOfferActionButtons();
      });
    },

    /*
     * notif_removeOffer:
     *   called during fair division setup, when player 1 removes a power from the offer
     */
    notif_removeOffer: function (n) {
      var _this = this;
      debug('Notif: removeOffer', n.args);

      // Create a dummy in the deck
      var dummy = this.createPowerSmall(0);
      // Find the right position
      var power = this.getPower(n.args.powerId);
      var nextPower = null;
      dojo.query('#cards-deck .power-card').some(function (div) {
        if (div.getAttribute('data-sort') > power.sort) {
          nextPower = div;
          return true;
        }
      });

      // Insert it
      if (nextPower != null) {
        dummy = dojo.place(dummy, nextPower, 'before');
      } else {
        dummy = dojo.place(dummy, 'cards-deck', 'last');
      }

      // Slide the real card to the position of the dummy
      var powerDivId = 'power-small-' + n.args.powerId;
      dummy.id = 'removeOffer-dummy';
      this.slide(powerDivId, dummy.id).then(function () {
        // Replace the dummy with the real card
        var powerDiv = dojo.place(powerDivId, dummy.id, 'replace');
        dojo.style(powerDiv, { top: null, left: null });
        dojo.removeClass(powerDiv, 'selected wait');
        _this._nMissingPowers++;

        _this.updateBannedPowers(n.args.banned);
        _this.buildOfferActionButtons();
      });
    },


    /*
     * onClickConfirmOffer:
     *   during fair division setup, when player 1 confirms they are done building the offer
     */
    onClickConfirmOffer: function () {
      if (!this.checkAction('confirmOffer')) {
        return false;
      }
      this.takeAction("confirmOffer");
    },


    ///////////////////////////
    //// Nyx's Night Power ////
    ///////////////////////////

    onEnteringStateChooseNyxNightPower: function (args) {
      var _this = this;
      this._displayedPower = null;
      dojo.empty('grid-detail');
      dojo.destroy('section-offer');
      dojo.empty('cards-deck');
      $('title-deck').textContent = dojo.string.substitute(_('Powers Available (${count}):'), { count: args.nyxDeck.length });
      args.nyxDeck.sort(this.comparePowerIdsByName);
      args.nyxDeck.forEach(function (powerId) {
        var div = dojo.place(_this.createPowerSmall(powerId, 'night'), 'cards-deck');
        dojo.connect(div, 'onclick', function (e) {
          return _this.onClickPowerSmall(powerId);
        });
      });
      this.buildOfferActionButtons();
    },

    addNyxNightPower: function (powerId) {
      this.gamedatas.nyxNightPower = powerId;
      var div = dojo.place(this.createPowerSmall(powerId, 'night'), 'play-area');
      div.id = 'power-nyxNight';
      if (this._focusedContainer != 'scene-container') {
        dojo.addClass(div, 'hide');
      }
      var powerDetail = this.createPowerDetail(powerId, 'night');
      this.addTooltipHtml(div.id, powerDetail);

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialogNyx');
      // match material.inc.php specialNames
      powerDialog.setTitle(_("Nyx's Night Power"));
      powerDialog.setContent(powerDetail);
      powerDialog.replaceCloseCallback(function () { powerDialog.hide(); });
      dojo.connect(div, "onclick", function (ev) { powerDialog.show(); });
    },


    /////////////////////////////
    //// Choose First Player ////
    /////////////////////////////
    onEnteringStateChooseFirstPlayer: function (args) {
      var _this = this;
      dojo.empty('power-choose-container');
      args.powers.sort(this.comparePowerIdsByName);
      args.powers.forEach(function (powerId) {
        var power = _this.getPower(powerId);
        var div = dojo.place(_this.createPowerDetail(powerId), 'power-choose-container');
        if (power.id == 66) { // Nyx: Also display the night power
          dojo.place(_this.createPowerDetail(_this.gamedatas.nyxNightPower, 'night'), div);
        }
        div.id = "power-choose-" + power.id;

        if (_this.isCurrentPlayerActive()) {
          dojo.addClass(div, 'clickable');
          dojo.connect(div, 'onclick', function (ev) {
            _this.onClickChooseFirstPlayer(powerId);
          });
          _this.addActionButton('buttonFirstPlayer' + powerId, _(power.name), function () {
            _this.onClickChooseFirstPlayer(powerId)
          }, null, false, powerId == args.suggestion ? 'blue' : 'gray');
        }
      });
    },

    /*
     * onClickChooseFirstPlayer: is called when the contestant clicked on the player who will start
     */
    onClickChooseFirstPlayer: function (powerId) {
      if (!this.checkAction('chooseFirstPlayer')) {
        return false;
      }
      this.takeAction("chooseFirstPlayer", { powerId: powerId });
    },


    //////////////////////
    //// Choose Power ////
    //////////////////////

    /*
     * powersPlayerChoose: in the fair division setup, each player then proceed to pick one card
     */
    onEnteringStatePowersPlayerChoose: function (args) {
      var _this = this;

      // Display remaining powers
      dojo.empty('power-choose-container');
      Object.values(args.offer)
        .sort(this.comparePowerCardsByName)
        .forEach(function (powerCard) {
          var power = _this.getPower(powerCard.id);
          var div = dojo.place(_this.createPowerDetail(powerCard.id), 'power-choose-container');
          if (power.id == 66) { // Nyx: Also display the night power
            dojo.place(_this.createPowerDetail(_this.gamedatas.nyxNightPower, 'night'), div);
          }
          if (powerCard.location_arg == 1) {
            var mark = document.createElement("div");
            mark.className = 'power-counter infinite';
            mark.textContent = '1';
            mark.title = dojo.string.substitute(_('${power_name} will start this game'), { power_name: power.name });
            div.append(mark);
          }
          div.id = "power-choose-" + power.id;

          if (_this.isCurrentPlayerActive()) {
            dojo.addClass(div, 'clickable');
            dojo.connect(div, 'onclick', function (e) {
              return _this.onClickChoosePower(power.id);
            });
            _this.addActionButton('buttonChoosePower' + power.id, _(power.name), function () {
              _this.onClickChoosePower(power.id)
            }, null, false, 'blue');
          }
        });
    },

    /*
     * onClickChoosePower:
     *   during fair division, when a player cladojo.place(ims a power
     */
    onClickChoosePower: function (powerId) {
      if (!this.checkAction('choosePower')) {
        return false;
      }
      this.takeAction("choosePower", { powerId: powerId });
    },


    /*
     * notif_powerAdded:
     *   called whenever a player gains a power
     */
    notif_powerAdded: function (n) {
      var _this = this;
      debug('Notif: power added', n.args);

      var playerId = n.args.player_id,
        powerId = n.args.power_id,
        reason = n.args.reason;
      this.addPower(playerId, powerId, reason);
    },

    /*
     * notif_powerRemoved:
     *   called whenever a player loses a power
     */
    notif_powerRemoved: function (n) {
      var _this = this;
      debug('Notif: power removed', n.args);

      var playerId = n.args.player_id,
        powerId = n.args.power_id,
        reason = n.args.reason;
      this.removePower(playerId, powerId, reason);
    },

    /*
     * notif_powerMoved:
     *   called whenever a power moves from one player to another (Circe)
     */
    notif_powerMoved: function (n) {
      var _this = this;
      debug('Notif: power moved', n.args);

      var oldPlayerId = n.args.player_id2,
        newPlayerId = n.args.player_id,
        powerId = n.args.power_id,
        reason = n.args.reason;
      this.addPower(newPlayerId, powerId, reason);
    },


    ////////////////////////////////////
    ////////////////////////////////////
    ////////    Golden Fleece   ////////
    ////////////////////////////////////
    ////////////////////////////////////

    addGoldenFleece: function (powerId) {
      this.gamedatas.goldenFleece = powerId;
      var powerDetail = this.createPowerDetail(powerId);
      var div = dojo.place(this.createPowerSmall(powerId), 'play-area');
      div.id = 'power-ram';
      this.addTooltipHtml(div.id, powerDetail);

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialogRam');
      // match material.inc.php specialNames
      powerDialog.setTitle(_('Golden Fleece power'));
      powerDialog.setContent(powerDetail);
      powerDialog.replaceCloseCallback(function () { powerDialog.hide(); });
      dojo.connect(div, "onclick", function (ev) { powerDialog.show(); });
    },

    notif_specialPowerSet: function (n) {
      debug('Notif: Special power was set', n.args);
      if (n.args.location == 'ram') {
        this.addGoldenFleece(n.args.powerId);
      } else if (n.args.location == 'nyxNight') {
        this.addNyxNightPower(n.args.powerId);
      }
    },


    ///////////////////////////////////////
    ///////////////////////////////////////
    ////////    Worker placement   ////////
    ///////////////////////////////////////
    ///////////////////////////////////////

    /*
     * playerPlaceWorker: the active player can place one worker on the board
     */
    onEnteringStatePlayerPlaceWorker: function (args) {
      this.worker = args.worker;
      this.board.makeClickable(args.accessibleSpaces, this.onClickPlaceWorker.bind(this), 'place');
      if (args.displayType && this.isCurrentPlayerActive()) {
        $('pagemaintitletext').innerHTML += " (" + (args.worker.type_arg[0] == 'f' ? _("female") : _("male")) + ")";
      }
    },

    /*
     * playerPlaceRam: the active player can place the Ram figure on the board
     */
    onEnteringStatePlayerPlaceRam: function (args) {
      this.worker = args.worker;
      this.board.makeClickable(args.accessibleSpaces, this.onClickPlaceWorker.bind(this), 'place');
    },

    /*
     * onClickPlaceWorker:
     * 	triggered after a click on a space to place new worker
     */
    onClickPlaceWorker: function (space) {
      // Check that this action is possible at this moment
      if (!this.checkAction('placeWorker')) {
        return false;
      }
      this.clearPossible();
      space.workerId = this.worker.id;
      this.takeAction("placeWorker", space);
    },


    /*
     * notif_workerPlaced:
     *   called whenever a new worker is placed on the board
     */
    notif_workerPlaced: function (n) {
      debug('Notif: new worker placed', n.args);
      this.board.addPiece(n.args.piece);
    },


    /////////////////////////////////////////
    /////////////////////////////////////////
    ///////////    Use Power    /////////////
    /////////////////////////////////////////
    /////////////////////////////////////////

    /*
     * onEnteringStatePlayerUsePower: the active player can use their (non-basic) power
     */
    onEnteringStatePlayerUsePower: function (args) {
      this._powerId = args.power;
      var power = this.getPower(args.power);
      var usePowerFn = this['usePower' + power.nameEnglish];
      if (typeof usePowerFn != 'function') {
        gameui.showMessage('Missing function: usePower' + power.nameEnglish, 'error');
        return;
      }
      usePowerFn.call(this, args);
    },

    usePowerCharon: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerAres: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerAdonis: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerNemesis: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },
    
    usePowerTartarus: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerMedea: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerJason: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerOdysseus: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerTheseus: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerEuropa: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerGaea: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerScylla: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerHydra: function (args) {
      this._action = 'playerBuild';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerProteus: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    /////////////////////////////////////////
    /////////////////////////////////////////
    ////////    Work : move / build  ////////
    /////////////////////////////////////////
    /////////////////////////////////////////

    /*
     * playerMove and playerBuild: the active player can/must move/build
     */
    onEnteringStatePlayerWork: function (args) {
      this._powerId = null;
      this.makeWorkersSelectable(args.workers);
    },

    onEnteringStatePlayerMove: function (args) {
      this._action = "playerMove";
      this.onEnteringStatePlayerWork(args);
    },

    onEnteringStatePlayerBuild: function (args) {
      this._action = "playerBuild";
      this.onEnteringStatePlayerWork(args);
    },

    /*
     * makeWorkersSelectable:
     */
    makeWorkersSelectable: function (workers) {
      this._selectableWorkers = workers.filter(function (worker) {
        return worker.works && worker.works.length > 0;
      });

      // If no worker can work => restart or resign
      if (this._selectableWorkers.length == 0) {
        this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate.descriptioncannot;
        this.updatePageTitle();
      } else if (this._selectableWorkers.length == 1) {
        // If only one worker can work, automatically select it
        this.onClickSelectWorker(this._selectableWorkers[0]);
      } else if (this._selectableWorkers.length > 1) {
        // Otherwise, let the user make the choice
        this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
      }
    },

    /*
     * onClickSelectWorker:
     * 	triggered after a click on a worker
     */
    onClickSelectWorker: function (worker) {
      this.clearPossible();

      // Select the worker
      this._selectedWorker = worker;
      if (worker.location == 'board') {
        // Highlight the worker if it's on the board
        // For Jason, Gaea, etc. the worker may not yet exist
        this.board.highlightPiece(worker);
      }
      // Let the user change selection (if any other choices)
      if (this._selectableWorkers.length > 1) {
        this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray');
      }

      this.board.makeClickable(worker.works, this.onClickSpace.bind(this), this._action);
    },


    /*
     * onClickCancelSelect:
     * 	triggered after a click on the action button "buttonReset".
     *  unselect the previously selected worker and make every worker selectable
     */
    onClickCancelSelect: function (evt) {
      dojo.stopEvent(evt);
      this.clearPossible();
      this._selectedWorker = null;
      this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
    },


    /*
     * onClickSpace:
     * 	triggered after a click on a space to either move/build/...
     *  a space may have some args denoting some choices (eg type of block for Atlas)
     */
    onClickSpace: function (space) {
      if (space.arg == null) {
        return this.onClickSpaceArg(space, null);
      } else if (space.arg.length == 1 && !space.dialog) {
        return this.onClickSpaceArg(space, space.arg[0]);
      }
      return this.dialogChooseArg(space);
    },

    /*
     * TODO
     */
    dialogChooseArg: function (space) {
      var _this = this;
      var dial = new ebg.popindialog();
      dial.create('chooseArg');
      dial.setTitle(_("Choose the building block"));

      // TODO : might be other choices ?
      space.arg.forEach(function (arg) {
        var div = dojo.place(_this.format_block('jstpl_argPrompt', {
          arg: arg
        }), 'popin_chooseArg_contents');

        dojo.connect(div, 'onclick', function (e) {
          dial.destroy();
          _this.onClickSpaceArg(space, arg);
        });
      });
      dial.show();
    },


    /*
     * TODO
     */
    onClickSpaceArg: function (space, arg) {
      if ((this._powerId == null && !this.checkAction(this._action))
        || (!this._powerId == null && !this.checkAction("use"))) {
        return;
      }

      var data = {
        workerId: this._selectedWorker.id,
        x: space.x,
        y: space.y,
        z: space.z,
        arg: arg
      };
      // Not using power => normal work
      if (this._powerId == null) {
        this.takeAction("work", data);
      } else {
        // Power work
        data.powerId = this._powerId;
        this.takeAction("usePowerWork", data);
      }

      this.clearPossible(); // Make sur to clear after sending ajax otherwise selectedWorker will be null
    },


    /*
     * onClickSkip: is called when the active player decide to skip work
     */
    onClickSkip: function () {
      if (!this.checkAction('skip')) {
        return;
      }
      var action = (this.gamedatas.gamestate.name == "playerUsePower") ? "skipPower" : "skipWork";
      this.takeAction(action);
      this.clearPossible();
    },


    /*
     * onClickCancel: is called when the active player decide to cancel previous works
     */
    onClickCancel: function () {
      if (!this.checkAction('cancel')) {
        return;
      }
      this.takeAction("cancelPreviousWorks");
      this.clearPossible();
    },


    /*
     * onClickConfirm: is called when the active player decide to confirm their turn
     */
    onClickConfirm: function () {
      if (!this.checkAction('confirm')) {
        return;
      }
      this.takeAction("confirmTurn");
    },


    /*
     * onClickResign: is called when the active player decide to resign (when cannot move/build)
     */
    onClickResign: function () {
      if (!this.checkAction('resign')) {
        return;
      }
      this.confirmationDialog(__("lang_mainsite", "You are about to concede this game. Are you sure?"), dojo.hitch(this, function () {
        this.takeAction("resign");
        this.clearPossible();
      }));
    },


    /////////////////////
    //// Work Notifs ////
    /////////////////////

    /*
     * notif_workerMoved:
     *   called whenever a worker is moved on the board
     */
    notif_workerMoved: function (n) {
      debug('Notif: worker moved', n.args);
      this.board.movePiece(n.args.piece, n.args.space);
    },

    /*
     * notif_blockBuilt:
     *   called whenever a new block is built
     */
    notif_blockBuilt: function (n) {
      debug('Notif: block built', n.args);
      this.board.addPiece(n.args.piece);
    },

    /*
     * notif_blockBuilt:
     *   called whenever a new block is built under a worker using Zeus
     */
    notif_blockBuiltUnder: function (n) {
      debug('Notif: block built under', n.args);
      this.board.addPieceUnder(n.args.piece, n.args.under);
    },


    /*
     * notif_pieceRemoved:
     *   called whenever a piece is removed/killed (eg Bia, Medusa, Ares)
     */
    notif_pieceRemoved: function (n) {
      debug('Notif: piece removed', n.args);
      this.board.removePiece(n.args.piece);
    },


    ///////////////////////////////////////
    ////////    Utility methods    ////////
    ///////////////////////////////////////

    slide: function slide(sourceId, targetId) {
      var _this = this;
      return new Promise(function (resolve, reject) {
        var animation = _this.slideToObject(sourceId, targetId);
        dojo.connect(animation, 'onEnd', resolve);
        animation.play();
      });
    },

    slideTemporary: function slideTemporary(phantom, sourceId, targetId, duration) {
      var _this = this;
      duration = duration || 1000;
      return new Promise(function (resolve, reject) {
        _this.slideTemporaryObject(phantom, document.body, sourceId, targetId, duration);
        setTimeout(resolve, duration);
      });
    },

    getPower: function getPower(powerId) {
      // Gets a power object ready to use in UI templates
      var power = this.gamedatas.powers[powerId] || {
        id: 0,
        sort: 0,
        counter: 0,
        golden: false,
        playerCount: '',
        tooltipGolden: '',
        tooltipPlayerCount: '',
        nameEnglish: '',
        name: '',
        title: '',
        text: '',
        type: '',
      };
      return power;
    },

    createMiniCard: function (playerId, powerId, cssClass) {
      var power = this.getPower(powerId);
      if (cssClass) {
        power = Object.assign({}, power); // copy
        power.type = (power.type || '') + ' ' + cssClass;
      }
      return dojo.place(this.format_block('jstpl_miniCard', power), 'power_container_' + playerId);
    },

    createPowerSmall: function (powerId, cssClass) {
      var power = this.getPower(powerId);
      if (cssClass) {
        power = Object.assign({}, power); // copy
        power.type = (power.type || '') + ' ' + cssClass;
      }
      return this.format_block('jstpl_powerSmall', power);
    },

    createPowerDetail: function (powerId, cssClass) {
      var power = this.getPower(powerId);
      if (cssClass) {
        power = Object.assign({}, power); // copy
        power.type = (power.type || '') + ' ' + cssClass;
      }
      return this.format_block('jstpl_powerDetail', power);
    },

    /*
     * focusContainer:
     * 	show and hide containers depending on state
     */
    focusContainer: function focusContainer() {
      var stateName = this.gamedatas.gamestate.name;
      var container = 'scene-container';
      switch (stateName) {
        case 'gameSetup':
        case 'powersSetup':
        case 'buildOffer':
        case 'chooseNyxNightPower':
          container = 'power-offer-container';
          break;
        case 'chooseFirstPlayer':
        case 'powersNextPlayerChoose':
        case 'powersPlayerChoose':
          container = 'power-choose-container';
          break;
      }

      // Stop if the container is already focused
      if (container == this._focusedContainer || (container == 'scene-container' && !this.isLoadingComplete)) {
        return;
      }

      debug('Focus container', 'state=' + stateName, 'container=' + container);
      dojo.style('power-offer-container', 'display', 'none');
      dojo.style('power-choose-container', 'display', 'none');
      if (container == 'scene-container') {
        dojo.empty('power-offer-container');
        dojo.empty('power-choose-container');
        dojo.removeClass('scene-container', 'fixed');
        var powerNyx = $('power-nyxNight');
        if (powerNyx) {
          dojo.removeClass(powerNyx, 'hide');
        }
        this.board.updateSize();
        this.board.enterScene();
      } else {
        dojo.addClass('scene-container', 'fixed');
        this.board.updateSize();
        dojo.style(container, 'display', 'flex');
      }
      this._focusedContainer = container;
    },


    /*
     * clearPossible:
     * 	clear every clickable space and any selected worker
     */
    clearPossible: function clearPossible() {
      this.removeActionButtons();
      this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args, true);
      this._selectedWorker = null;
      this.board.clearClickable();
      this.board.clearHighlights();
    },


    ///////////////////////////////////////////////////
    //////   Reaction to cometD notifications   ///////
    ///////////////////////////////////////////////////

    /*
     * setupNotifications:
     *  In this method, you associate each of your game notifications with your local method to handle it.
     *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
     */
    setupNotifications: function () {
      var notifs = [
        ['cancel', 1000],
        ['addOffer', 500],
        ['removeOffer', 500],
        ['powerAdded', 1000],
        ['powerRemoved', 1000],
        ['powerMoved', 1000],
        ['workerPlaced', 1000],
        ['workerMoved', 1600],
        ['blockBuilt', 1000],
        ['specialPowerSet', 10],
        ['blockBuiltUnder', 2000],// Happens with Zeus
        ['pieceRemoved', 2000], // Happens with Bia, Ares, Medusa
        ['updatePowerUI', 10], // Happens with Morpheus, Chaos
        ['loadBug', 10], // used in studio only
      ];

      var _this = this;
      notifs.forEach(function (notif) {
        var functionname = "notif_" + notif[0];
        dojo.subscribe(notif[0], _this, functionname);
        _this.notifqueue.setSynchronous(notif[0], notif[1]);

        // xxxInstant notification runs same function without delay
        dojo.subscribe(notif[0] + 'Instant', _this, functionname);
        _this.notifqueue.setSynchronous(notif[0] + 'Instant', 10);
      });
    },

    notif_loadBug: function (n) {
      function fetchNextUrl() {
        var url = n.args.urls.shift();
        debug('Fetching URL', url);
        dojo.xhrGet({
          url: url,
          load: function (success) {
            debug('Success for URL', url, success);
            if (n.args.urls.length > 0) {
              fetchNextUrl();
            } else {
              debug('Done, reloading page');
              window.location.reload();
            }
          }
        });
      }

      debug('Notif: load bug', n.args);
      fetchNextUrl();
    },
  });
});
