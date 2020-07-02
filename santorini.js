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
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };
var isMobile = function () {
  var body = document.getElementById("ebd-body");
  return body != null && body.classList.contains('mobile_version');
};


define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter", "ebg/stock", "ebg/scrollmap"], function (dojo, declare) {

  // Dojo ShrinkSafe does not support named function expressions
  // If you need to use this.inherited(), define the function here (not inside "return")
  function santorini_adaptStatusBar() {
    // Handle "position: fixed" for power detail (match page title)
    this.inherited(santorini_adaptStatusBar, arguments);
    var isFixed = dojo.hasClass("page-title", "fixed-page-title");
    dojo.toggleClass("grid-detail", "fixed", isFixed);
  }

  return declare("bgagame.santorini", ebg.core.gamegui, {
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

      // Setup the board (3d scene using threejs)
      dojo.place(this.format_block('jstpl_scene', {}), 'overall-content');
      this.board = new Board($('scene-container'), URL); // Setup player boards
      this.setupPreference();

      var target = document.getElementById('loader_mask');
      var observer = new MutationObserver(function (mutations) {
        if (target.style.display == 'none') {
          _this.onLoaderOff();
        }
      });
      observer.observe(target, { attributes: true, attributeFilter: ['style'] });

      // Setup powers
      Object.values(gamedatas.powers)
        .sort(function (power1, power2) {
          return _(power1.name).localeCompare(_(power2.name))
        }).forEach(function (power, index) {
          // For JS code: Keep English name, add sort order
          power.sort = index;
          power.nameEnglish = power.name;

          // For HTML template: Translate text, add counter
          power.name = _(power.name);
          power.title = _(power.title);
          power.text = '<p>' + power.text.map(function (text) {
            return _(text).replace(/\[/g, '<b>').replace(/\]/g, '</b>');
          }).join('</p><p>') + '</p>';
          power.type = power.hero ? 'hero' : '';
          power.counter = power.counter || 0;
        });
      gamedatas.fplayers.forEach(function (player) {
        dojo.place(_this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
        player.powers.forEach(function (powerId) {
          _this.addPower(player.id, powerId, 'init', false);
        });
      });

      // Setup workers and buildings
      gamedatas.placedPieces.forEach(function (piece) {
        _this.board.addPiece(piece);
      });

      // Setup golden fleece
      if (gamedatas.goldenFleece) {
        this.addGoldenFleece(gamedatas.goldenFleece);
      }

      // Handle for cancelled notification messages
      dojo.subscribe('addMoveToLog', this, 'santorini_addMoveToLog');

      // Setup game notifications
      this.setupNotifications();
    },

    comparePowersByName: function (power1, power2) {
      return power1.sort - power2.sort;
    },

    comparePowerIdsByName: function (id1, id2) {
      return gameui.gamedatas.powers[id1].sort - gameui.gamedatas.powers[id2].sort;
    },

    setupPreference: function () {
      var _this = this;
      var preferenceSelect = $('preference_control_100');
      var updatePreference = function () {
        var value = preferenceSelect.options[preferenceSelect.selectedIndex].value;
        _this.board.toggleCoordsHelpers(value == 1);
      };

      dojo.connect(preferenceSelect, 'onchange', updatePreference);
      updatePreference();
    },

    onLoaderOff: function () {
      this.onScreenWidthChange();
      if (this._focusedContainer == 'powers-offer') {
        dojo.style('power-offer-container', 'opacity', '1');
      } else if (this._focusedContainer == 'powers-choose') {
        dojo.style('power-choose-container', 'opacity', '1');
      }

      if (this._focusedContainer == 'board') {
        this.board.enterScene();
      } else {
        this.board.onLoad();
      }
    },

    // TODO
    onScreenWidthChange: function () {
      dojo.style('page-content', 'zoom', 'normal');
      if ($('scene-container')) {
        dojo.style('santorini-overlay', 'width', document.getElementById("left-side").offsetWidth + "px");
        dojo.style('3d-scene', 'marginTop', ($('page-content').getBoundingClientRect()['top'] - $('overall-content').getBoundingClientRect()['top']) + "px");
        dojo.style('play-area', 'min-height', (Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0) - ($('3d-scene') ? dojo.style('3d-scene', 'marginTop') : 100)) + "px");
        this.board.updateSize();
      }
    },

    adaptStatusBar: santorini_adaptStatusBar,

		/*
		 * notif_cancel:
		 *   called whenever a player restart their turn
		 */
    notif_cancel: function (n) {
      debug('Notif: cancel turn', n.args);
      this.board.diff(n.args.placedPieces);
      this.cancelNotifications(n.args.moveIds);
    },

    /*
     * cancelNotifications: cancel past notification log messages the given move IDs
     */
    cancelNotifications: function (moveIds) {
      for (var logId in this.log_to_move_id) {
        var moveId = +this.log_to_move_id[logId];
        if (moveIds.includes(moveId)) {
          debug('Cancel notification message for move ID ' + moveId + ', log ID ' + logId);
          dojo.addClass('log_' + logId, 'cancel');
        }
      }
    },

    /*
     * addMoveToLog: called by BGA framework when a new notification message is logged.
     * cancel it immediately if needed.
     */
    santorini_addMoveToLog: function (logId, moveId) {
      if (this.gamedatas.cancelMoveIds && this.gamedatas.cancelMoveIds.includes(+moveId)) {
        debug('Cancel notification message for move ID ' + moveId + ', log ID ' + logId);
        dojo.addClass('log_' + logId, 'cancel');
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
      var power = this.getPower(powerId);
      var powerDetail = this.createPowerDetail(powerId);
      var card = this.createMiniCard(playerId, powerId);
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
        var dummy = this.createMiniCard(playerId, powerId);
        dummy.id = 'miniCard-dummy';
        dojo.style(dummy, 'position', 'absolute');
        var animationTarget = (reason == 'ram' ? 'power-ram' : 'topbar');
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
      var animationTarget = (reason == 'ram' ? 'power-ram' : 'topbar');
      this.slideToObjectAndDestroy(card, animationTarget);
    },

		/*
		 * notif_updatePowerUI:
		 *   called whenever a power UI is updated (eg Morpheus)
		 */
    notif_updatePowerUI: function (n) {
      debug('Notif: updating power UI', n.args);
      this.gamedatas.powers[n.args.powerId].counter = n.args.counter;
      var q = dojo.query('#mini-card-' + n.args.playerId + "-" + n.args.powerId + ' .power-counter');
      if (q.length > 0) {
        q[0].textContent = n.args.counter;
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

      // Stop here if it's not the current player's turn for some states
      if (["playerUsePower", "playerPlaceWorker", "playerPlaceRam", "playerMove", "playerBuild", "confirmTurn", "gameEnd"].includes(stateName)) {
        this.focusContainer('board');
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

      // Don't remove the power cards
      if (stateName == 'powersPlayerChoose') {
        return;
      }

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

      if ((stateName == "playerMove" || stateName == "playerBuild" || stateName == "playerUsePower")) {
        if (args.skippable) {
          this.addActionButton('buttonSkip', _('Skip'), 'onClickSkip', null, false, 'gray');
        }
        if (args.cancelable) {
          this.addActionButton('buttonCancel', _('Restart turn'), 'onClickCancel', null, false, 'gray');
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
      if (isDebug) {
        debug('Ignoring startActionTimer(' + buttonId + ') because isDebug=true');
        return;
      }

      var _this = this;
      this.actionTimerLabel = $(buttonId).innerHTML;
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
      this.focusContainer('powers-offer');

      // Display selected powers, sorted by name
      args.offer.sort(this.comparePowerIdsByName);
      args.offer.forEach(function (powerId) {
        var div = dojo.place(_this.createPowerSmall(powerId), 'cards-offer');
        div.classList.add('selected');
        dojo.connect(div, 'onclick', function (e) {
          return _this.onClickPowerSmall(powerId);
        });
      });

      this._nMissingPowers = args.count - args.offer.length;

      // Display remaining powers, sorted by name
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
      var count = this.gamedatas.gamestate.args.offer.length + this.gamedatas.gamestate.args.deck.length;
      var countOffer = dojo.query('#cards-offer .power-card').length;
      var countDeck = count - countOffer;
      $('title-offer').textContent = dojo.string.substitute(_('Powers On Offer (${count}):'), { count: countOffer });
      $('title-deck').textContent = dojo.string.substitute(_('Powers Available (${count}):'), { count: countDeck });

      if (!this.isCurrentPlayerActive()) {
        return;
      }
      this.removeActionButtons();

      if (this._displayedPower) {
        var powerDiv = $('power-small-' + this._displayedPower),
          isBanned = powerDiv.classList.contains('banned'),
          isSelected = powerDiv.classList.contains('selected'),
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
        isBanned = powerDiv.classList.contains('banned'),
        isDisplayed = powerDiv.classList.contains('displayed'),
        isSelected = powerDiv.classList.contains('selected'),
        isWait = powerDiv.classList.contains('wait'); // Everyone may view details on first click
      this._displayedPower = powerId;

      if (!isDisplayed) {
        // Mark only this card as displayed
        dojo.query('.power-card.small.displayed').removeClass('displayed');
        powerDiv.classList.add('displayed');
        dojo.place(this.createPowerDetail(powerId), 'grid-detail', 'only');
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
      $('power-small-' + this._displayedPower).classList.add('wait');
      this.takeAction("addOffer", { powerId: this._displayedPower });
    },

    removeOffer: function () {
      $('power-small-' + this._displayedPower).classList.add('wait');
      this.takeAction("removeOffer", { powerId: this._displayedPower });
    },


    /*
     * notif_addOffer:
     *   called during fair division setup, when player 1 adds a power to the offer
     */
    notif_addOffer: function (n) {
      var _this = this;
      debug('Notif: addOffer', n.args);

      // Create a dummy in the offer that will be replaced by the actual power
      var dummy = dojo.place(this.createPowerSmall(0), 'cards-offer');
      dummy.id = 'addOffer-dummy';

      // Slide the real card to the position of the dummy
      var powerDivId = 'power-small-' + n.args.powerId;
      this.slide(powerDivId, dummy.id).then(function () {
        // Replace the dummy with the real card
        var powerDiv = dojo.place(powerDivId, dummy.id, 'replace');
        powerDiv.style = '';
        powerDiv.classList.add('selected');
        powerDiv.classList.remove('wait');
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
        powerDiv.style = '';
        powerDiv.classList.remove('selected');
        powerDiv.classList.remove('wait');
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


    /////////////////////////////
    //// Choose First Player ////
    /////////////////////////////
    onEnteringStateChooseFirstPlayer: function (args) {
      var _this = this;
      this.focusContainer('powers-choose');
      args.powers.forEach(function (powerId) {
        var power = _this.getPower(powerId);
        var div = dojo.place(_this.createPowerDetail(powerId), 'power-choose-container');
        div.id = "power-choose-" + power.id;

        if (_this.isCurrentPlayerActive()) {
          dojo.addClass(div, 'clickable');
          dojo.connect(div, 'onclick', function (ev) {
            _this.onClickChooseFirstPlayer(powerId);
          });
          _this.addActionButton('buttonFirstPlayer' + powerId, _(power.name), function () {
            _this.onClickChooseFirstPlayer(powerId)
          }, null, false, 'blue');
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
      this.focusContainer('powers-choose');

      // Display remeaining powers
      args.offer.forEach(function (powerCard) {
        var power = _this.getPower(powerCard.id);
        var div = dojo.place(_this.createPowerDetail(powerCard.id), 'power-choose-container');
        if (powerCard.location_arg == 1) {
          var mark = document.createElement("div");
          mark.className = 'power-counter';
          mark.textContent = '1';
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
      var powerDetail = this.createPowerDetail(powerId);
      var div = dojo.place(this.createPowerSmall(powerId), 'play-area');
      div.id = 'power-ram';
      this.addTooltipHtml(div.id, powerDetail);

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialogRam');
      powerDialog.setTitle(_("Ram's power"));
      powerDialog.setContent(powerDetail);
      powerDialog.replaceCloseCallback(function () { powerDialog.hide(); });
      dojo.connect(div, "onclick", function (ev) { powerDialog.show(); });
    },


    notif_ramPowerSet: function (n) {
      debug('Notif: ram power was set', n.args);
      this.addGoldenFleece(n.args.powerId);
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
        this.gamedatas.gamestate.descriptionmyturn = this._action == "playerMove" ? _("You cannot move") : _("You cannot build");
        this.updatePageTitle();
        this.addActionButton('buttonResign', _('Resign'), this.onClickResign.bind(this), null, false, 'gray');
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

      // Select the worker, highlight it and let the use change selection (if any other choices)
      this._selectedWorker = worker;
      this.board.highlightPiece(worker);
      if (this._selectableWorkers.length > 1) {
        this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray');
      }
      // TODO : automatically choose if only one space ?

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
      this.takeAction("resign");
      this.clearPossible();
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
     * notif_workerSwitched:
     *   called whenever two workers are switched using Apollo
     */
    notif_workerSwitched: function (n) {
      debug('Notif: worker switched', n.args);
      this.board.switchPiece(n.args.piece1, n.args.piece2);
    },

    /*
     * notif_blockBuilt:
     *   called whenever a new block is built under a worker using Zeus
     */
    notif_blockBuiltUnder: function (n) {
      debug('Notif: block built under', n.args);
      this.board.addPieceUnder(n.args.piece);
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

    /*
     * // TODO:
     */
    slide: function slide(sourceId, targetId) {
      var _this = this;
      return new Promise(function (resolve, reject) {
        var animation = _this.slideToObject(sourceId, targetId);
        dojo.connect(animation, 'onEnd', resolve);
        animation.play();
      });
    },


    /*
     * // TODO:
     */
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
        nameEnglish: '',
        name: '',
        title: '',
        text: '',
        type: '',
      };
      return power;
    },

    createMiniCard: function (playerId, powerId) {
      var power = this.getPower(powerId);
      return dojo.place(this.format_block('jstpl_miniCard', power), 'power_container_' + playerId);
    },

    createPowerSmall: function (powerId) {
      var power = this.getPower(powerId);
      return this.format_block('jstpl_powerSmall', power);
    },

    createPowerDetail: function (powerId) {
      var power = this.getPower(powerId);
      return this.format_block('jstpl_powerDetail', power);
    },

    /*
     * focusContainer:
     * 	show and hide containers depending on state
     */
    focusContainer: function focusContainer(container) {
      if (this._focusedContainer != null && this._focusedContainer == container) {
        return;
      }

      if (this._focusedContainer != null) {
        if (container == "board") {
          this.board.enterScene();
        } else if (container == "powers-choose") {
          dojo.style('power-choose-container', 'opacity', '1');
        }
      }

      this._focusedContainer = container;
      dojo.style('power-offer-container', 'display', container == 'powers-offer' ? 'flex' : 'none');
      dojo.style('power-choose-container', 'display', container == 'powers-choose' ? 'flex' : 'none');
      dojo.style('play-area', 'display', 'block');
    },


    /*
     * clearPossible:
     * 	clear every clickable space and any selected worker
     */
    clearPossible: function clearPossible() {
      this.removeActionButtons();
      this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args, true);

      dojo.empty('grid-powers');
      dojo.query('#power-detail').removeClass().addClass('power-card power-0');
      dojo.empty('power-choose-container');

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
        ['ramPowerSet', 1000],
        ['workerSwitched', 1600], 	// Happens with Apollo
        ['blockBuiltUnder', 2000],// Happens with Zeus
        ['pieceRemoved', 2000], // Happens with Bia, Ares, Medusa
        ['updatePowerUI', 10], // Happens with Morpheus, Chaos
      ];

      var _this = this;
      notifs.forEach(function (notif) {
        var functionname = "notif_" + notif[0];
        dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
        _this.notifqueue.setSynchronous(notif[0], notif[1]);
      });
    }
  });
});
