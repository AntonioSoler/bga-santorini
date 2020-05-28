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
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter", "ebg/stock", "ebg/scrollmap"], function (dojo, declare) {
  return declare("bgagame.santorini", ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor: function () { },

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
      var container = document.getElementById('scene-container');
      this.board = new Board(container, URL); // Setup player boards

      gamedatas.fplayers.forEach(function (player) {
        dojo.place(_this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
        player.powers.forEach(function (powerId) {
          return _this.addPowerToPlayer(player.id, powerId);
        });
      });

      // Setup workers and buildings
      gamedatas.placedPieces.forEach(function(piece){
				_this.board.addPiece(piece);
			});

      // Setup game notifications
      this.setupNotifications();
    },

		// TODO
		onScreenWidthChange: function() {
			dojo.style('page-content', 'zoom', 'normal' );
		},


		/*
		 * notif_cancel:
		 *   called whenever a player restart its turn
		 */
    notif_cancel: function (n) {
      debug('Notif: cancel turn', n.args);
			this.board.diff(n.args.placedPieces);
    },


    /*
     * addPowerToPlayer:
     * 	add a power card to given player
     * params:
     *  - object piece: main infos are type, x,y,z
     */
    addPowerToPlayer: function (playerId, powerId) {
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
    onEnteringState: function (stateName, args) {
      debug('Entering state: ' + stateName, args);

			// Update gamestate description when skippable
			if (["playerMove", "playerBuild"].includes(stateName) && args.args.skippable) {
				this.updatePageTitleSkippableWork(stateName);
			}

      // Stop here if it's not the current player's turn for some states
      if (["playerUsePower", "playerPlaceWorker", "playerMove", "playerBuild", "gameEnd"].includes(stateName)) {
        this.focusContainer('board');
        if (!this.isCurrentPlayerActive()) return;
      }

      // Call appropriate method
      var methodName = "onEnteringState" + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined)
        this[methodName](args.args);
    },

		/*
		 * TODO : description
		 */
		updatePageTitleSkippableWork : function(stateName) {
			this.gamedatas.gamestate.descriptionmyturn = this.pageTitleSpan(true) + " " + ( (stateName == "playerMove")? _("may move a worker") : _("may build") );
			this.gamedatas.gamestate.description = this.pageTitleSpan(false) + " " + ( (stateName == "playerMove")? _("may move a worker") : _("may build") );
			this.updatePageTitle();
		},

		pageTitleSpan: function(myturn){
			var pId = this.gamedatas.gamestate.active_player;
			var color = this.gamedatas.players[pId].color;
			var text = myturn ? __("lang_mainsite", "You") : this.gamedatas.players[pId].name;
			return "<span style=\"font-weight:bold;color:#" + color + ";\">" + text + "</span>";
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
      if (stateName == 'powersPlayerChoose')
        return;

      this.clearPossible();
    },

    /*
     * onUpdateActionButtons:
     * 	called by BGA framework before onEnteringState
     *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
     */
    onUpdateActionButtons: function (stateName, args) {
      debug('Update action buttons: ' + stateName, args); // Make sure it the player's turn

      if (!this.isCurrentPlayerActive())
        return;

      if ((stateName == "playerMove" || stateName == "playerBuild" || stateName == "playerUsePower")){
				if(args.skippable) {
        	this.addActionButton('buttonSkip', _('Skip'), 'onClickSkip', null, false, 'gray');
				}

				if(args.cancelable) {
        	this.addActionButton('buttonCancel', _('Restart turn'), 'onClickCancel', null, false, 'gray');
				}
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

      this._nMissingPowers = this.gamedatas.fplayers.length - args.offer.length;

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
      if (this.isCurrentPlayerActive()) {
        this.removeActionButtons();

        if (this._nMissingPowers == 0) {
          this.addActionButton('buttonConfirmOffer', _('Confirm'), 'onClickConfirmOffer', null, false, 'blue');
        }
      }
    },

		/*
		 * updateBannedPowers: display only "selectable" powers
		 */
		updateBannedPowers: function(bannedIds){
			dojo.query(".power-card.small").removeClass("banned");
			bannedIds.forEach(function(powerId) {
        if($("power-small-" + powerId))
				  dojo.addClass("power-small-" + powerId, "banned");
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

      if (!isDisplayed) {
        // Mark only this card as displayed
        dojo.query('.power-card.small.displayed').removeClass('displayed');
        powerDiv.classList.add('displayed'); // Display the detail

        var power = this.getPower(powerId);
        dojo.place(this.format_block('jstpl_powerDetail', power), 'grid-detail', 'only');
      }
      // Otherwise, active player may select/unselect the power
      else if (!isWait && isActive) {
        // Already selected => unselect it
        if (isSelected) {
          this.ajaxcall("/santorini/santorini/removeOffer.html", {
            powerId: powerId
          }, this, function (res) { });
          powerDiv.classList.add('wait');
        }
        // Not yet select + still need powers => select it
        else if (this._nMissingPowers > 0 && !isBanned) {
          this.ajaxcall("/santorini/santorini/addOffer.html", {
            powerId: powerId
          }, this, function (res) { });
          powerDiv.classList.add('wait');
        }
      }
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
        if (acc != null) return acc;
        if (div.getAttribute('data-power') > n.args.powerId) return div;
      }, null);

      // Insert it
      if (nextPower !== null)
        dummy = dojo.place(dummy, nextPower, 'before');
      else
        dummy = dojo.place(dummy, $('cards-deck'), 'last');

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
      if (!this.checkAction('confirmOffer')) return false;
      this.ajaxcall("/santorini/santorini/confirmOffer.html", {}, this, function (res) { });
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
      args.offer.forEach(function (powerId) {
        var power = _this.getPower(powerId);
        var div = dojo.place(_this.format_block('jstpl_powerDetail', power), $('power-choose-container'));
        div.id = "power-choose-" + power.id;

        if (_this.isCurrentPlayerActive()) {
          dojo.style(div, "cursor", "pointer");
          dojo.connect(div, 'onclick', function (e) {
            return _this.onClickChoosePower(power.id);
          });
        }
      });
    },


    /*
     * onClickChoosePower:
     *   during fair division, when a player cladojo.place(ims a power
     */
    onClickChoosePower: function (powerId) {
      if (!this.checkAction('choosePower'))
        return false;

      this.ajaxcall("/santorini/santorini/choosePower.html", {
        powerId: powerId
      }, this, function (res) { });
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
      if (powerChooseCard == null)
        this.addPowerToPlayer(playerId, powerId);
      else {
        powerChooseCard.style.height = powerChooseCard.offsetHeight + "px";
        powerChooseCard.classList.add("power-dummy");
        var phantom = this.format_block('jstpl_powerDetail', this.getPower(powerId));
        this.slideTemporary(phantom, "power-choose-" + powerId, 'power_container_' + playerId).then(function () {
          dojo.destroy(powerChooseCard);
          _this.addPowerToPlayer(playerId, powerId);
        });
      }
    },


    /////////////////////////////
    //// Choose First Player ////
    /////////////////////////////

    onEnteringStateChooseFirstPlayer: function(args){
      this.focusContainer(null);
			if (this.isCurrentPlayerActive())
      	this.chooseFirstPlayerActionsButtons(args.players);
    },

    /*
     * chooseFirstPlayerActionsButtons: let the contestant choose who will start
     */
    chooseFirstPlayerActionsButtons: function(players){
      var _this = this;
      players.forEach(function(player){
        _this.addActionButton('buttonFirstPlayer' + player.id, player.name, function(){ _this.onClickChooseFirstPlayer(player) }, null, false, 'gray');
      });
    },

    /*
     * onClickChooseFirstPlayer: is called when the contestant clicked on the player who will start
     */
    onClickChooseFirstPlayer: function(player){
      if (!this.checkAction('chooseFirstPlayer'))
        return false;

      this.ajaxcall("/santorini/santorini/chooseFirstPlayer.html", {playerId: player.id}, this, function (res) { });
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
    },


    /*
     * onClickPlaceWorker:
     * 	triggered after a click on a space to place new worker
     */
    onClickPlaceWorker: function (space) {
      // Check that this action is possible at this moment
      if (!this.checkAction('placeWorker'))
        return false;

      this.clearPossible();
      space.workerId = this.worker.id;
      this.ajaxcall("/santorini/santorini/placeWorker.html", space, this, function (res) { });
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
		powersIds:{
			APOLLO : 1,
			ARTEMIS : 2,
			ATHENA : 3,
			ATLAS : 4,
			DEMETER : 5,
			HEPHAESTUS : 6,
			HERMES : 7,
			MINOTAUR : 8,
			PAN : 9,
			PROMETHEUS : 10,
			APHRODITE : 11,
			ARES : 12,
			BIA : 13,
			CHAOS : 14,
			CHARON : 15,
			CHRONUS : 16,
			CIRCE : 17,
			DIONYSUS : 18,
			EROS : 19,
			HERA : 20,
			HESTIA : 21,
			HYPNUS : 22,
			LIMUS : 23,
			MEDUSA : 24,
			MORPHEUS : 25,
			PERSEPHONE : 26,
			POSEIDON : 27,
			SELENE : 28,
			TRITON : 29,
			ZEUS : 30,
			AEOLUS : 31,
			CHARYBDIS : 32,
			CLIO : 33,
			EUROPA : 34,
			GAEA : 35,
			GRAEAE : 36,
			HADES : 37,
			HARPIES : 38,
			HECATE : 39,
			MOERAE : 40,
			NEMESIS : 41,
			SIREN : 42,
			TARTARUS : 43,
			TERPSICHORE : 44,
			URANIA : 45,
			ACHILLES : 46,
			ADONIS : 47,
			ATALANTA : 48,
			BELLEROPHON : 49,
			HERACLES : 50,
			JASON : 51,
			MEDEA : 52,
			ODYSSEUS : 53,
			POLYPHEMUS : 54,
			THESEUS : 55,
		},

		/*
     * onEnteringStatePlayerUsePower: the active player can use its (non-basic) power
     */
		onEnteringStatePlayerUsePower: function(args){
			this._powerId = args.power;

			for(var power in this.powersIds){
				if(this.powersIds[power] == args.power)
					this['usePower' + power.charAt(0) + power.slice(1).toLowerCase()](args);
			}
		},


		usePowerCharon: function(args){
			this._action = 'playerMove';
			this.makeWorkersSelectable(args.workers);
		},

		usePowerAres: function(args){
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
		makeWorkersSelectable: function(workers){
			this._selectableWorkers = workers.filter(function (worker) {
        return worker.works && worker.works.length > 0;
      });

			// If only one worker can work, automatically select it
			if (this._selectableWorkers.length == 1)
				this.onClickSelectWorker(this._selectableWorkers[0]);
			// Otherwise, let the user make the choice
			else if (this._selectableWorkers.length > 1)
				this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
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
      if (this._selectableWorkers.length > 1)
        this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray');
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
      if (space.arg == null)
        return this.onClickSpaceArg(space, null);
      if (space.arg.length == 1)
        return this.onClickSpaceArg(space, space.arg[0]);

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
			|| (!this._powerId == null && !this.checkAction("use")))
        return;

			var data = {
        workerId: this._selectedWorker.id,
        x: space.x,
        y: space.y,
        z: space.z,
        arg: arg
      };
			// Not using power => normal work
			if(this._powerId == null){
      	this.ajaxcall("/santorini/santorini/work.html", data, this, function (res) { });
			}
			// Power work
			else {
				data.powerId = this._powerId;
				this.ajaxcall("/santorini/santorini/usePowerWork.html", data, this, function (res) { });
			}

      this.clearPossible(); // Make sur to clear after sending ajax otherwise selectedWorker will be null
    },


    /*
     * onClickSkip: is called when the active player decide to skip work
     */
    onClickSkip: function () {
      if (!this.checkAction('skip'))
        return;

			var url = (this.gamedatas.gamestate.name == "playerUsePower")? "skipPower" : "skipWork";
      this.ajaxcall("/santorini/santorini/" + url + ".html", {}, this, function (res) { });
      this.clearPossible();
    },


		/*
     * onClickCancel: is called when the active player decide to cancel previous works
     */
    onClickCancel: function () {
      if (!this.checkAction('cancel'))
        return;

      this.ajaxcall("/santorini/santorini/cancelPreviousWorks.html", {}, this, function (res) { });
      this.clearPossible();
    },


    /////////////////////
    //// Work Notifs ////
    /////////////////////

		/*
		 * notif_automatic:
		 *   called whenever a worker has only one choice
		 */
    notif_automatic: function (n) {
      debug('Notif: automatic work incoming', n.args);
    },

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
     * clearPossible:
     * 	clear every clickable space and any selected worker
     */
    clearPossible: function clearPossible() {
      this.removeActionButtons();
      this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);

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
    setupNotifications: function setupNotifications() {
      var notifs = [
				['cancel', 1000],
        ['automatic', 1000],
        ['addOffer', 500],
        ['removeOffer', 500],
        ['powerAdded', 1200],
        ['workerPlaced', 1000],
        ['workerMoved', 2000],
        ['blockBuilt', 1000],
        ['workerSwitched', 2000], 	// Happens with Apollo
        ['blockBuiltUnder', 2000],// Happens with Zeus
        ['pieceRemoved', 2000] // Happens with Bia, Ares, Medusa
      ];

      var _this = this;
      notifs.forEach(function (notif) {
        dojo.subscribe(notif[0], _this, "notif_" + notif[0]);
        _this.notifqueue.setSynchronous(notif[0], notif[1]);
      });
    }
  });
});
