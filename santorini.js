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
var isDebug = true;
var debug = isDebug ? console.info.bind(window.console) : function () { };
var isMobile = function () {
  var body = document.getElementById("ebd-body");
  return body != null && body.classList.contains('mobile_version');
};


define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter", "ebg/stock", "ebg/scrollmap"], function (dojo, declare) {
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

      // Mobile fix position of card selection
      // TODO this needs to happen AFTER the BGA adaptStatusBar()
      dojo.connect(window, "scroll", this, this.onScroll.bind(this));

      // Setup the board (3d scene using threejs)
      dojo.place(this.format_block('jstpl_scene', {}), $('overall-content'));
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
      this.setupPowers(gamedatas.fplayers);

      // Setup workers and buildings
      gamedatas.placedPieces.forEach(function (piece) {
        _this.board.addPiece(piece);
      });

      // Setup golden fleece
      if (gamedatas.goldenFleece) {
        this.addGoldenFleece(gamedatas.goldenFleece);
      }

      // Setup game notifications
      this.setupNotifications();
    },


    setupPreference: function () {
      var _this = this;
      var preferenceSelect = $('preference_control_100');
      var updatePreference = function () {
        var value = preferenceSelect.options[preferenceSelect.selectedIndex].value;
        _this.board.toggleCoordsHelpers(value == 1);
      };

      dojo.connect(preferenceSelect, 'onchange', updatePreference);;
      updatePreference();
    },

    onLoaderOff: function () {
      this.onScreenWidthChange();
      if (this._focusedContainer == 'powers-offer') {
        dojo.style('power-offer-container', 'opacity', '1');
      } else if (this._focusedContainer == 'powers-choose') {
        dojo.style('power-choose-container', 'opacity', '1');
      } else if (this._focusedContainer == 'board') {
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

    onScroll: function () {
      var isFixed = dojo.hasClass("page-title", "fixed-page-title");
      dojo.toggleClass("grid-detail", "fixed", isFixed);
    },


		/*
		 * notif_cancel:
		 *   called whenever a player restart their turn
		 */
    notif_cancel: function (n) {
      debug('Notif: cancel turn', n.args);
      this.board.diff(n.args.placedPieces);
    },


		/*
		 * setupPowers: give each player their corresponding powers
		 */
    setupPowers: function (players) {
      var _this = this;
      players.forEach(function (player) {
        var container = $('power_container_' + player.id);
        if (container != null) {
          dojo.empty(container);
        } else {
          dojo.place(_this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
        }
        player.powers.forEach(function (powerId) {
          _this.addPowerToPlayer(player.id, powerId, false);
        });
      });
    },


    /*
     * addPowerToPlayer:
     * 	add a power card to given player
     * params:
     *  - object piece: main infos are type, x,y,z
     */
    addPowerToPlayer: function (playerId, powerId, showDialog) {
      var power = this.getPower(powerId);
      var card = dojo.place(this.format_block('jstpl_miniCard', power), 'power_container_' + playerId);
      card.id = "mini-card-" + playerId + "-" + powerId;
      this.addTooltipHtml(card.id, this.format_block('jstpl_powerDetail', power));

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialog-' + playerId + "-" + powerId);
      powerDialog.setTitle(playerId == this.player_id ? _("Your power") : _("Opponent's power"));
      powerDialog.setContent(this.format_block('jstpl_powerDetail', this.getPower(powerId)));
      powerDialog.replaceCloseCallback(function () { powerDialog.hide(); });
      dojo.connect(card, "onclick", function (ev) { powerDialog.show(); });
      if (showDialog && playerId == this.player_id) {
        powerDialog.show();
      }

      if (powerId == this.powersIds.MORPHEUS || powerId == this.powersIds.CHAOS) {
        var data = {
          playerId: playerId,
          powerId: powerId,
          n: power.counter,
        };
        dojo.place(this.format_block('jstpl_powerCounter', data), 'mini-card-' + playerId + "-" + powerId);
      }
    },


		/*
		 * notif_updatePowerUI:
		 *   called whenever a power UI is updated (eg Morpheus)
		 */
    notif_updatePowerUI: function (n) {
      debug('Notif: updating power UI', n.args);
      if (n.args.powerId == this.powersIds.MORPHEUS || n.args.powerId == this.powersIds.CHAOS) {
        var div = $('power-counter-' + n.args.playerId + "-" + n.args.powerId);
        if (div) {
          div.innerHTML = n.args.counter;
        }
      }
    },


		/*
		 * notif_powersChanged:
		 *   called whenever powers are changed (eg Circe, Chaos)
		 */
    notif_powersChanged: function (n) {
      debug('Notif: chaging powers', n.args);
      this.setupPowers(n.args.fplayers);
    },

		/*
		 * TODO description
		 */
    takeAction: function (action, data, callback) {
      data = data || {};
      data.lock = true;
      callback = callback || function (res) { };
      this.stopActionTimer();
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

      // Display selected powers
      args.offer.forEach(function (powerId) {
        var div = dojo.place(_this.format_block('jstpl_powerSmall', _this.getPower(powerId)), $('cards-offer'));
        div.classList.add('selected');
        dojo.connect(div, 'onclick', function (e) {
          return _this.onClickPowerSmall(powerId);
        });
      });

      this._nMissingPowers = args.count - args.offer.length;

      // Display remeaining powers
      args.deck.forEach(function (powerId) {
        var div = dojo.place(_this.format_block('jstpl_powerSmall', _this.getPower(powerId)), $('cards-deck'));
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
      if (!this.isCurrentPlayerActive()) {
        return;
      }

      this.removeActionButtons();

      if (this._displayedPower) {
        var powerDiv = $('power-small-' + this._displayedPower),
          isBanned = powerDiv.classList.contains('banned'),
          isSelected = powerDiv.classList.contains('selected');

        if (isSelected) {
          this.addActionButton('buttonRemoveFromOffer', _('Remove from offer'), this.removeOffer.bind(this), null, false, 'red');
        } else if (this._nMissingPowers > 0 && !isBanned) {
          this.addActionButton('buttonAddToOffer', _('Add to offer'), this.addOffer.bind(this), null, false, 'blue');
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
        powerDiv.classList.add('displayed'); // Display the detail

        var power = this.getPower(powerId);
        dojo.place(this.format_block('jstpl_powerDetail', power), 'grid-detail', 'only');
      } else if (!isWait && isActive) {
        // Otherwise, active player may select/unselect the power
        // Already selected => unselect it
        if (isSelected) {
          this.removeOffer();
        } else if (this._nMissingPowers > 0 && !isBanned) {
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
      var dummy = dojo.place(this.format_block('jstpl_powerSmall', this.getPower(0)), $('cards-offer'));
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
      var dummy = this.format_block('jstpl_powerSmall', this.getPower(0));
      // Find the right position
      var nextPower = dojo.query('#cards-deck .power-card').reduce(function (acc, div) {
        if (acc != null) {
          return acc;
        }
        if (div.getAttribute('data-power') > n.args.powerId) {
          return div;
        }
      }, null);

      // Insert it
      if (nextPower !== null) {
        dummy = dojo.place(dummy, nextPower, 'before');
      } else {
        dummy = dojo.place(dummy, $('cards-deck'), 'last');
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
        var div = dojo.place(_this.format_block('jstpl_powerDetail', power), $('power-choose-container'));
        div.id = "power-choose-" + power.id;

        if (_this.isCurrentPlayerActive()) {
          dojo.style(div, "cursor", "pointer");
          dojo.connect(div, 'onclick', function (ev) {
            _this.onClickChooseFirstPlayer(powerId);
          });
          _this.addActionButton('buttonFirstPlayer' + powerId, _this.getPower(powerId).name, function () { _this.onClickChooseFirstPlayer(powerId) }, null, false, 'blue');
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
        var div = dojo.place(_this.format_block('jstpl_powerDetail', power), $('power-choose-container'));
        if (powerCard.location_arg == 1) {
          var mark = document.createElement("div");
          mark.className = "first";
          mark.innerHTML = '1';
          div.append(mark);
        }
        div.id = "power-choose-" + power.id;

        if (_this.isCurrentPlayerActive()) {
          dojo.style(div, "cursor", "pointer");
          dojo.connect(div, 'onclick', function (e) {
            return _this.onClickChoosePower(power.id);
          });
          _this.addActionButton('buttonChoosePower' + power.id, _this.getPower(power.id).name, function () { _this.onClickChoosePower(power.id) }, null, false, 'blue');
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
     *   called whenever a player choose a power on a Fair Division process
     */
    notif_powerAdded: function (n) {
      var _this = this;
      debug('Notif: a power was chosen', n.args);

      var playerId = n.args.player_id,
        powerId = n.args.power_id;
      var powerChooseCard = $("power-choose-" + powerId);
      if (powerChooseCard == null) {
        this.addPowerToPlayer(playerId, powerId);
      } else {
        powerChooseCard.style.height = powerChooseCard.offsetHeight + "px";
        powerChooseCard.classList.add("power-dummy");
        var phantom = this.format_block('jstpl_powerDetail', this.getPower(powerId));
        this.slideTemporary(phantom, "power-choose-" + powerId, 'power_container_' + playerId).then(function () {
          dojo.destroy(powerChooseCard);
          _this.addPowerToPlayer(playerId, powerId);
        });
      }
    },



    ////////////////////////////////////
    ////////////////////////////////////
    ////////    Golden Fleece   ////////
    ////////////////////////////////////
    ////////////////////////////////////

    addGoldenFleece: function (powerId) {
      var power = this.getPower(powerId);
      var div = dojo.place(this.format_block('jstpl_powerSmall', power), $('play-area'));
      div.classList.add('golden');
      this.addTooltipHtml('power-small-' + power.id, this.format_block('jstpl_powerDetail', power));

      var powerDialog = new ebg.popindialog();
      powerDialog.create('powerDialogRam');
      powerDialog.setTitle(_("Ram's power"));
      powerDialog.setContent(this.format_block('jstpl_powerDetail', power));
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
    powersIds: {
      APOLLO: 1,
      ARTEMIS: 2,
      ATHENA: 3,
      ATLAS: 4,
      DEMETER: 5,
      HEPHAESTUS: 6,
      HERMES: 7,
      MINOTAUR: 8,
      PAN: 9,
      PROMETHEUS: 10,
      APHRODITE: 11,
      ARES: 12,
      BIA: 13,
      CHAOS: 14,
      CHARON: 15,
      CHRONUS: 16,
      CIRCE: 17,
      DIONYSUS: 18,
      EROS: 19,
      HERA: 20,
      HESTIA: 21,
      HYPNUS: 22,
      LIMUS: 23,
      MEDUSA: 24,
      MORPHEUS: 25,
      PERSEPHONE: 26,
      POSEIDON: 27,
      SELENE: 28,
      TRITON: 29,
      ZEUS: 30,
      AEOLUS: 31,
      CHARYBDIS: 32,
      CLIO: 33,
      EUROPA: 34,
      GAEA: 35,
      GRAEAE: 36,
      HADES: 37,
      HARPIES: 38,
      HECATE: 39,
      MOERAE: 40,
      NEMESIS: 41,
      SIREN: 42,
      TARTARUS: 43,
      TERPSICHORE: 44,
      URANIA: 45,
      ACHILLES: 46,
      ADONIS: 47,
      ATALANTA: 48,
      BELLEROPHON: 49,
      HERACLES: 50,
      JASON: 51,
      MEDEA: 52,
      ODYSSEUS: 53,
      POLYPHEMUS: 54,
      THESEUS: 55,
    },

		/*
     * onEnteringStatePlayerUsePower: the active player can use their (non-basic) power
     */
    onEnteringStatePlayerUsePower: function (args) {
      this._powerId = args.power;

      for (var power in this.powersIds) {
        if (this.powersIds[power] == args.power) {
          this['usePower' + power.charAt(0) + power.slice(1).toLowerCase()](args);
        }
      }
    },


    usePowerCharon: function (args) {
      this._action = 'playerMove';
      this.makeWorkersSelectable(args.workers);
    },

    usePowerAres: function (args) {
      this._action = 'playerBuild';
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
        this.gamedatas.gamestate.descriptionmyturn = this._action == "playerMove" ? _("You cannot move a worker") : _("You cannot build");
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
      } else if (space.arg.length == 1) {
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
        }), $('popin_chooseArg_contents'));

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
        name: '',
        title: '',
        text: [],
        implemented: "implemented"
      };
      power.type = power.hero ? 'hero' : '';

      // TODO map for translation
      power.textList = power.text.map(function (t) {
        t = _(t);
        t = t.replace(/\[/g, '<b>').replace(/\]/g, '</b>');
        return t;
      }).join('</p><p>');
      return power;
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
        ['powerAdded', 1200],
        ['workerPlaced', 1000],
        ['workerMoved', 1600],
        ['blockBuilt', 1000],
        ['ramPowerSet', 1000],
        ['workerSwitched', 1600], 	// Happens with Apollo
        ['blockBuiltUnder', 2000],// Happens with Zeus
        ['pieceRemoved', 2000], // Happens with Bia, Ares, Medusa
        ['updatePowerUI', 10], // Happens with Morpheus
        ['powersChanged', 10], // Happens with Circe
      ];

      var _this = this;
      notifs.forEach(function (notif) {
        dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
        _this.notifqueue.setSynchronous(notif[0], notif[1]);
      });
    }
  });
});
