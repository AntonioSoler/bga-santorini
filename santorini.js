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
var debug = isDebug ? console.info.bind(window.console) : function () {};
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter", "ebg/stock", "ebg/scrollmap"], function (dojo, declare) {
  return declare("bgagame.santorini", ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor: function constructor() {},

    /*
     * Setup:
     *  This method set up the game user interface according to current game situation specified in parameters
     *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
     *
     * Params :
     *  - mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
     */
    setup: function setup(gamedatas) {
      var _this = this;

      debug('SETUP', gamedatas); // Setup the board (3d scene using threejs)

      var container = document.getElementById('scene-container');
      this.board = new Board(container, URL); // Setup player boards

      gamedatas.fplayers.forEach(function (player) {
        dojo.place(_this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
        player.powers.forEach(function (powerId) {
          return _this.addPowerToPlayer(player.id, powerId);
        });
      }); // Setup workers and buildings

      gamedatas.placedPieces.forEach(this.createPiece.bind(this)); // Setup game notifications

      this.setupNotifications();
    },

    /*
     * addPowerToPlayer:
     * 	add a power card to given player
     * params:
     *  - object piece: main infos are type, x,y,z
     */
    addPowerToPlayer: function addPowerToPlayer(playerId, powerId) {
      var power = this.getPower(powerId);
      var card = dojo.place(this.format_block('jstpl_miniCard', power), 'power_container_' + playerId);
      card.id = "mini-card-" + playerId + "-" + powerId;
      this.addTooltipHtml(card.id, this.format_block('jstpl_powerDetail', power));
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
    onEnteringState: function onEnteringState(stateName, args) {
      debug('Entering state: ' + stateName, args); // Stop here if it's not the current player's turn for some states

      if (["playerPlaceWorker", "playerMove", "playerBuild"].includes(stateName)) {
        this.focusContainer('board');
        if (!this.isCurrentPlayerActive()) return;
      } // Call appropriate method


      var methodName = "onEnteringState" + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },

    /*
     * onLeavingState:
     * 	this method is called each time we are leaving a game state.
     *
     * params:
     *  - str stateName : name of the state we are leaving
     */
    onLeavingState: function onLeavingState(stateName) {
      debug('Leaving state: ' + stateName); // Don't remove the power cards

      if (stateName == 'powersPlayerChoose') return;
      this.clearPossible();
    },

    /*
     * onUpdateActionButtons:
     * 	called by BGA framework before onEnteringState
     *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
     */
    onUpdateActionButtons: function onUpdateActionButtons(stateName, args) {
      debug('Update action buttons: ' + stateName, args); // Make sure it the player's turn

      if (!this.isCurrentPlayerActive()) return;

      if ((stateName == "playerMove" || stateName == "playerBuild") && args.skippable) {
        this.addActionButton('buttonSkip', _('Skip'), 'onClickSkip', null, false, 'gray');
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
    //  - contestant choose the first player to place its worker TODO
    //////////////////////////////////////
    /////////////////////
    //// Build Offer ////
    /////////////////////

    /*
     * BuildOffer: in the fair division setup,the contestant can select #players powers from available powers (depending on game option)
     */
    onEnteringStateBuildOffer: function onEnteringStateBuildOffer(args) {
      var _this2 = this;

      this.focusContainer('powers-offer'); // Display selected powers

      args.offer.forEach(function (powerId) {
        var div = dojo.place(_this2.format_block('jstpl_powerSmall', _this2.getPower(powerId)), $('cards-offer'));
        div.classList.add('selected');
        dojo.connect(div, 'onclick', function (e) {
          return _this2.onClickPowerSmall(powerId);
        });
      });
      this._nMissingPowers = this.gamedatas.fplayers.length - args.offer.length; // Display remeaining powers

      args.deck.forEach(function (powerId) {
        var div = dojo.place(_this2.format_block('jstpl_powerSmall', _this2.getPower(powerId)), $('cards-deck'));
        dojo.connect(div, 'onclick', function (e) {
          return _this2.onClickPowerSmall(powerId);
        });
      });
      this.buildOfferActionButtons();
    },

    /*
     * buildOfferActionButtons: show confirm button if the count is correct
     */
    buildOfferActionButtons: function buildOfferActionButtons() {
      if (this.isCurrentPlayerActive()) {
        this.removeActionButtons();

        if (this._nMissingPowers == 0) {
          this.addActionButton('buttonConfirmOffer', _('Confirm'), 'onClickConfirmOffer', null, false, 'blue');
        }
      }
    },

    /*
     * onClickPowerSmall:
     * 	 during fair division setup, when clicking on a small card while building the offer
     */
    onClickPowerSmall: function onClickPowerSmall(powerId) {
      var powerDiv = $('power-small-' + powerId),
          isActive = this.isCurrentPlayerActive(),
          isDisplayed = powerDiv.classList.contains('displayed'),
          isSelected = powerDiv.classList.contains('selected'),
          isWait = powerDiv.classList.contains('wait'); // Everyone may view details on first click

      if (!isDisplayed) {
        // Mark only this card as displayed
        dojo.query('.power-card.small.displayed').removeClass('displayed');
        powerDiv.classList.add('displayed'); // Display the detail

        var power = this.getPower(powerId);
        dojo.place(this.format_block('jstpl_powerDetail', power), 'grid-detail', 'only');
      } // Otherwise, active player may select/unselect the power
      else if (!isWait && isActive) {
          // Already selected => unselect it
          if (isSelected) {
            this.ajaxcall("/santorini/santorini/removeOffer.html", {
              powerId: powerId
            }, this, function (res) {});
            powerDiv.classList.add('wait');
          } // Not yet select + still need powers => select it
          else if (this._nMissingPowers > 0) {
              this.ajaxcall("/santorini/santorini/addOffer.html", {
                powerId: powerId
              }, this, function (res) {});
              powerDiv.classList.add('wait');
            }
        }
    },

    /*
     * notif_addOffer:
     *   called during fair division setup, when player 1 adds a power to the offer
     */
    notif_addOffer: function notif_addOffer(n) {
      var _this3 = this;

      debug('Notif: addOffer', n.args); // Create a dummy in the offer that will be replaced by the actual power

      var dummy = dojo.place(this.format_block('jstpl_powerSmall', this.getPower(0)), $('cards-offer'));
      dummy.id = 'addOffer-dummy'; // Slide the real card to the position of the dummy

      var powerDivId = 'power-small-' + n.args.powerId;
      this.slide(powerDivId, dummy.id).then(function () {
        // Replace the dummy with the real card
        var powerDiv = dojo.place(powerDivId, dummy.id, 'replace');
        powerDiv.style = '';
        powerDiv.classList.add('selected');
        powerDiv.classList.remove('wait');
        _this3._nMissingPowers--;

        _this3.buildOfferActionButtons();
      });
    },

    /*
     * notif_removeOffer:
     *   called during fair division setup, when player 1 removes a power from the offer
     */
    notif_removeOffer: function notif_removeOffer(n) {
      var _this4 = this;

      debug('Notif: removeOffer', n.args); // Create a dummy in the deck

      var dummy = this.format_block('jstpl_powerSmall', this.getPower(0));
      dummy.id = 'removeOffer-dummy'; // Find the right position

      var nextPower = dojo.query('#cards-deck .power-card').reduce(function (acc, div) {
        if (acc != null) return acc;
        if (div.getAttribute('data-power') > n.args.powerId) return div;
      }, null); // Insert it

      if (nextPower !== null) dummy = dojo.place(dummy, nextPower, 'before');else dummy = dojo.place(dummy, $('cards-deck'), 'last'); // Slide the real card to the position of the dummy

      var powerDivId = 'power-small-' + n.args.powerId;
      this.slide(powerDivId, dummy.id).then(function () {
        // Replace the dummy with the real card
        var powerDiv = dojo.place(powerDivId, dummy.id, 'replace');
        powerDiv.style = '';
        powerDiv.classList.remove('selected');
        powerDiv.classList.remove('wait');
        _this4._nMissingPowers++;

        _this4.buildOfferActionButtons();
      });
    },

    /*
     * onClickConfirmOffer:
     *   during fair division setup, when player 1 confirms they are done building the offer
     */
    onClickConfirmOffer: function onClickConfirmOffer() {
      if (!this.checkAction('confirmOffer')) return false;
      this.ajaxcall("/santorini/santorini/confirmOffer.html", {}, this, function (res) {});
    },
    //////////////////////
    //// Choose Power ////
    //////////////////////

    /*
     * powersPlayerChoose: in the fair division setup, each player then proceed to pick one card
     */
    onEnteringStatePowersPlayerChoose: function onEnteringStatePowersPlayerChoose(args) {
      var _this5 = this;

      this.focusContainer('powers-choose'); // Display remeaining powers

      args.offer.forEach(function (powerId) {
        var power = _this5.getPower(powerId);

        var div = dojo.place(_this5.format_block('jstpl_powerDetail', power), $('power-choose-container'));
        div.id = "power-choose-" + power.id;

        if (_this5.isCurrentPlayerActive()) {
          dojo.style(div, "cursor", "pointer");
          dojo.connect(div, 'onclick', function (e) {
            return _this5.onClickChoosePower(power.id);
          });
        }
      });
    },

    /*
     * onClickChoosePower:
     *   during fair division, when a player cladojo.place(ims a power
     */
    onClickChoosePower: function onClickChoosePower(powerId) {
      if (!this.checkAction('choosePower')) return false;
      this.ajaxcall("/santorini/santorini/choosePower.html", {
        powerId: powerId
      }, this, function (res) {});
    },

    /*
     * notif_powerAdded:
     *   called whenever a player choose a power on a Fair Division process
     */
    notif_powerAdded: function notif_powerAdded(n) {
      var _this6 = this;

      debug('Notif: a power was chosen', n.args);
      var playerId = n.args.player_id,
          powerId = n.args.power_id;
      var powerChooseCard = $("power-choose-" + powerId);
      if (powerChooseCard == null) this.addPowerToPlayer(playerId, powerId);else {
        powerChooseCard.style.height = powerChooseCard.offsetHeight + "px";
        powerChooseCard.classList.add("power-dummy");
        var phantom = this.format_block('jstpl_powerDetail', this.getPower(powerId));
        this.slideTemporary(phantom, "power-choose-" + powerId, 'power_container_' + playerId).then(function () {
          dojo.destroy(powerChooseCard);

          _this6.addPowerToPlayer(playerId, powerId);
        });
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
    onEnteringStatePlayerPlaceWorker: function onEnteringStatePlayerPlaceWorker(args) {
      this.worker = args.worker;
      this.board.makeClickable(args.accessibleSpaces, this.onClickPlaceWorker.bind(this), 'place');
    },

    /*
     * onClickPlaceWorker:
     * 	triggered after a click on a space to place new worker
     */
    onClickPlaceWorker: function onClickPlaceWorker(space) {
      // Check that this action is possible at this moment
      if (!this.checkAction('placeWorker')) return false;
      this.clearPossible();
      space.workerId = this.worker.id;
      this.ajaxcall("/santorini/santorini/placeWorker.html", space, this, function (res) {});
    },

    /*
     * notif_workerPlaced:
     *   called whenever a new worker is placed on the board
     */
    notif_workerPlaced: function notif_workerPlaced(n) {
      debug('Notif: new worker placed', n.args);
      this.createPiece(n.args.piece);
    },
    /////////////////////////////////////////
    /////////////////////////////////////////
    ////////    Work : move / build  ////////
    /////////////////////////////////////////
    /////////////////////////////////////////

    /*
     * playerMove and playerBuild r: the active player can/must move/build
     */
    onEnteringStatePlayerWork: function onEnteringStatePlayerWork(args) {
      // TODO : this filtering should be useless now since filtering is done on backend
      this._selectableWorkers = args.workers.filter(function (worker) {
        return worker.works.length > 0;
      }); // If only one worker can work, automatically select it

      if (this._selectableWorkers.length == 1) this.onClickSelectWorker(this._selectableWorkers[0]); // Otherwise, let the user make the choice
      else if (this._selectableWorkers.length > 1) this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
    },
    onEnteringStatePlayerMove: function onEnteringStatePlayerMove(args) {
      this._action = "playerMove";
      this.onEnteringStatePlayerWork(args);
    },
    onEnteringStatePlayerBuild: function onEnteringStatePlayerBuild(args) {
      this._action = "playerBuild";
      this.onEnteringStatePlayerWork(args);
    },

    /*
     * onClickSelectWorker:
     * 	triggered after a click on a worker
     */
    onClickSelectWorker: function onClickSelectWorker(worker) {
      this.clearPossible(); // Select the worker, highlight it and let the use change selection (if any other choices)

      this._selectedWorker = worker;
      this.board.highlightPiece(worker);
      if (this._selectableWorkers.length > 1) this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray'); // TODO : automatically choose if only one space ?

      this.board.makeClickable(worker.works, this.onClickSpace.bind(this), this._action);
    },

    /*
     * onClickCancelSelect:
     * 	triggered after a click on the action button "buttonReset".
     *  unselect the previously selected worker and make every worker selectable
     */
    onClickCancelSelect: function onClickCancelSelect(evt) {
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
    onClickSpace: function onClickSpace(space) {
      if (space.arg == null) return this.onClickSpaceArg(space, null);
      if (space.arg.length == 1) return this.onClickSpaceArg(space, space.arg[0]);
      return this.dialogChooseArg(space);
    },

    /*
     * TODO
     */
    dialogChooseArg: function dialogChooseArg(space) {
      var _this7 = this;

      var dial = new ebg.popindialog();
      dial.create('chooseArg');
      dial.setTitle(_("Choose the building block")); // TODO : might be other choices ?

      space.arg.forEach(function (arg) {
        var div = dojo.place(_this7.format_block('jstpl_argPrompt', {
          arg: arg
        }), $('popin_chooseArg_contents'));
        dojo.connect(div, 'onclick', function (e) {
          dial.destroy();

          _this7.onClickSpaceArg(space, arg);
        });
      });
      dial.show();
    },

    /*
     * TODO
     */
    onClickSpaceArg: function onClickSpaceArg(space, arg) {
      if (!this.checkAction(this._action)) return;
      this.ajaxcall("/santorini/santorini/work.html", {
        workerId: this._selectedWorker.id,
        x: space.x,
        y: space.y,
        z: space.z,
        arg: arg
      }, this, function (res) {});
      this.clearPossible(); // Make sur to clear after sending ajax otherwise selectedWorker will be null
    },

    /*
     * onClickSkip: is called when the active player decide to skip work
     */
    onClickSkip: function onClickSkip() {
      if (!this.checkAction('skip')) return;
      this.ajaxcall("/santorini/santorini/skipWork.html", {}, this, function (res) {});
      this.clearPossible();
    },
    /////////////////////
    //// Work Notifs ////
    /////////////////////

    /*
     * notif_workerMoved:
     *   called whenever a worker is moved on the board
     */
    notif_workerMoved: function notif_workerMoved(n) {
      debug('Notif: worker moved', n.args);
      this.board.movePiece(n.args.piece, n.args.space);
    },

    /*
     * notif_blockBuilt:
     *   called whenever a new block is built
     */
    notif_blockBuilt: function notif_blockBuilt(n) {
      console.log('Notif: block built', n.args);
      this.createPiece(n.args.piece);
    },

    /*
     * notif_workerSwitched:
     *   called whenever two workers are switched using Apollo
     */
    notif_workerSwitched: function notif_workerSwitched(n) {
      console.info('Notif: worker switched', n.args);
      this.board.switchPiece(n.args.piece1, n.args.piece2);
    },

    /*
     * notif_workerPushed:
     *   called whenever a worker is pushed using Minotaur
     */
    notif_workerPushed: function notif_workerPushed(n) {
      console.info('Notif: worker pushed', n.args);
      this.board.movePiece(n.args.piece2, n.args.space, 1500);
      this.board.movePiece(n.args.piece1, n.args.piece2);
    },
    ///////////////////////////////////////
    ////////    Utility methods    ////////
    ///////////////////////////////////////

    /*
     * // TODO:
     */
    slide: function slide(sourceId, targetId) {
      var _this8 = this;

      return new Promise(function (resolve, reject) {
        var animation = _this8.slideToObject(sourceId, targetId);

        dojo.connect(animation, 'onEnd', resolve);
        animation.play();
      });
    },

    /*
     * // TODO:
     */
    slideTemporary: function slideTemporary(phantom, sourceId, targetId, duration) {
      var _this9 = this;

      duration = duration || 800;
      return new Promise(function (resolve, reject) {
        _this9.slideTemporaryObject(phantom, document.body, sourceId, targetId, duration);

        setTimeout(resolve, duration);
      });
    },
    getPower: function getPower(powerId) {
      // Gets a power object ready to use in UI templates
      var power = this.gamedatas.powers[powerId] || {
        id: 0,
        name: '',
        title: '',
        text: []
      };
      power.type = power.hero ? 'hero' : ''; // TODO map for translation

      power.textList = power.text.join('</li><li>');
      return power;
    },

    /*
     * focusContainer:
     * 	show and hide containers depending on state
     */
    focusContainer: function focusContainer(container) {
      dojo.style('power-offer-container', 'display', container == 'powers-offer' ? 'flex' : 'none');
      dojo.style('power-choose-container', 'display', container == 'powers-choose' ? 'flex' : 'none');
      dojo.style('play-area', 'display', container == 'board' ? 'block' : 'none');
    },

    /*
     * createPiece:
     * 	add a piece to the board (with falldown animation)
     * params:
     *  - object piece: main infos are type, x,y,z
     */
    createPiece: function createPiece(piece) {
      piece.name = piece.type;
      if (piece.type == "worker") piece.name = piece.type_arg + piece.type;
      this.board.addPiece(piece);
    },

    /*
     * clearPossible:
     * 	clear every clickable space and any selected worker
     */
    clearPossible: function clearPossible() {
      this.removeActionButtons();
      this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
      this._selectedWorker = null;
      dojo.empty('grid-powers');
      dojo.query('#power-detail').removeClass().addClass('power-card power-0');
      dojo.empty('power-choose-container');
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
    setupNotifications: function setupNotifications() {
      dojo.subscribe('addOffer', this, "notif_addOffer");
      this.notifqueue.setSynchronous('addOffer', 500);
      dojo.subscribe('removeOffer', this, "notif_removeOffer");
      this.notifqueue.setSynchronous('removeOffer', 500);
      dojo.subscribe('powerAdded', this, "notif_powerAdded");
      this.notifqueue.setSynchronous('powerAdded', 1000);
      dojo.subscribe('workerPlaced', this, 'notif_workerPlaced');
      this.notifqueue.setSynchronous('workerPlaced', 1000);
      dojo.subscribe('workerMoved', this, 'notif_workerMoved');
      this.notifqueue.setSynchronous('workerMoved', 2000);
      dojo.subscribe('blockBuilt', this, 'notif_blockBuilt');
      this.notifqueue.setSynchronous('blockBuilt', 1000); // Happens with Apollo

      dojo.subscribe('workerSwitched', this, 'notif_workerSwitched');
      this.notifqueue.setSynchronous('workerSwitched', 2000); // Happens with Minotaur

      dojo.subscribe('workerPushed', this, 'notif_workerPushed');
      this.notifqueue.setSynchronous('workerPushed', 2000);
    }
  });
});
