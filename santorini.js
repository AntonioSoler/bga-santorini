/**
	*------
	* BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
	* santorini implementation : (c) Morgalad &
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


const isDebug = false;
var debug = isDebug? console.info.bind(window.console) : function(){};

define([
	"dojo", "dojo/_base/declare",
	"ebg/core/gamegui",
	"ebg/counter",
	"ebg/stock",
	"ebg/scrollmap"
], function(dojo, declare) {
	return declare("bgagame.santorini", ebg.core.gamegui, {

/*
 * Constructor
 */
constructor: function() {

},

/*
 * Setup:
 *  This method set up the game user interface according to current game situation specified in parameters
 *  The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
 *
 * Params :
 *  - mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
 */
setup: function(gamedatas) {
	debug('SETUP', gamedatas);

	// Setup the board (3d scene using threejs)
	var	container = document.getElementById('scene-container');
	this.board = new Board(container, URL);

	// Setup player boards
	gamedatas.fplayers.forEach(player => {
		dojo.place(this.format_block('jstpl_powerContainer', player), 'player_board_' + player.id);
		player.powers.forEach(powerId => this.addPowerToPlayer(player.id, powerId) );
	});

	// Setup workers and buildings
	gamedatas.placedPieces.forEach(this.createPiece.bind(this));

	// Setup game notifications
	this.setupNotifications();
},

getPower: function(powerId) {
	// Gets a power object ready to use in UI templates
	var power = this.gamedatas.powers[powerId] || {
		id : 0,
		name: '',
		title: '',
		text: [],
	};
	power.type = power.hero ? 'hero' : '';
	// TODO map for translation
	power.textList = power.text.join('</li><li>');
	return power;
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
onEnteringState: function(stateName, args) {
	debug('Entering state: ' + stateName, args.args);

	if (stateName == 'powersDivide') {
		this.focusContainer('powers-select');
		this._selectedPowers = [];
		// TODO: The argument should be only power ID
		args.args.powers.forEach(powerxxx => {
			var power = this.getPower(powerxxx.id);
			var div = dojo.place(this.format_block('jstpl_powerSelect', power), $('power-select-available') );
			dojo.connect(div, 'onclick', e => this.onClickSelectPower(power.id) );
		});

	} else if (stateName == 'powersPlayerChoose') {
		this.focusContainer('powers-choose');
		// TODO: The argument should be only power ID
		args.args.powers.forEach(powerxxx => {
			var power = this.getPower(powerxxx.id);
			var div = dojo.place(this.format_block('jstpl_powerDetail', power), $('power-choose-container') );
			div.id = "power-choose-" + power.id;
			if (this.isCurrentPlayerActive()) {
				dojo.style(div, "cursor", "pointer");
				dojo.connect(div, 'onclick', e => this.onClickChoosePower(power.id) );
			}
		});

	} else {
		this.focusContainer('board');
	}

	// Stop here if it's not the current player's turn
	if (!this.isCurrentPlayerActive()) {
		return;
	}

	if (stateName == 'playerPlaceWorker') {
		// Place a worker
		this.worker = args.args.worker;
		this.board.makeClickable(args.args.accessibleSpaces, this.onClickPlaceWorker.bind(this), 'place');

	} else if (stateName == "playerMove" || stateName == "playerBuild") {
		// Move a worker or build
		this._action = stateName;
		this._selectableWorkers = args.args.workers.filter(worker => worker.works.length > 0);
		if(this._selectableWorkers.length > 1)
			this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
		else if(this._selectableWorkers.length == 1)
			this.onClickSelectWorker(this._selectableWorkers[0]);
	}
},


/*
 * onLeavingState:
 * 	this method is called each time we are leaving a game state.
 *
 * params:
 *  - str stateName : name of the state we are leaving
 */
onLeavingState: function(stateName) {
	debug('Leaving state: ' + stateName);
	this.clearPossible();
},



/*
 * onUpdateActionButtons:
 * 	TODO when is this called ?
 *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function(stateName, args) {
	debug('Update action buttons: ' + stateName, args);

	// Make sure it the player's turn
	if (!this.isCurrentPlayerActive())
		return;

	if((stateName == "playerMove" || stateName == "playerBuild") && args.skippable){
		this.addActionButton('buttonSkip', _('Skip'), 'onClickSkip', null, false, 'gray');
	}
},




///////////////////////////////////////
////////    Utility methods    ////////
///////////////////////////////////////

/*
 * focusContainer:
 * 	show and hide containers depending on state
 */
focusContainer: function(container){
	dojo.style( 'power-select-container', 'display', container == 'powers-select'? 'flex' : 'none');
	dojo.style( 'power-choose-container', 'display', container == 'powers-choose'? 'flex' : 'none');
	dojo.style( 'play-area', 'display', container == 'board'?  'block' : 'none');
},

/*
 * addPowerToPlayer:
 * 	add a power card to given player
 * params:
 *  - object piece: main infos are type, x,y,z
 */
addPowerToPlayer: function(playerId, powerId) {
	var power = this.getPower(powerId);
	var card = dojo.place(this.format_block('jstpl_miniCard', power), 'power_container_' + playerId);
	card.id = "mini-card-" + playerId + "-" + powerId;
	this.addTooltipHtml( card.id, this.format_block('jstpl_powerDetail', power) );
},


/*
 * createPiece:
 * 	add a piece to the board (with falldown animation)
 * params:
 *  - object piece: main infos are type, x,y,z
 */
createPiece: function(piece) {
	piece.name = piece.type;
	if(piece.type == "worker")
		piece.name = piece.type_arg + piece.type;

	this.board.addPiece(piece);
},


/*
 * clearPossible:
 * 	clear every clickable space and any selected worker
 */
clearPossible: function() {
	this.removeActionButtons();
	this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);

	this._selectedWorker = null;
	dojo.empty('grid-powers');
	dojo.query('#power-detail').removeClass().addClass('power-card power-0');
	dojo.empty('power-choose-container');

	this.board.clearClickable();
	this.board.clearHighlights();
},

displayPower: function(powerId) {
	// Mark only this card as displayed
	dojo.query('.power-card.small.displayed').removeClass('displayed');
	var powerDiv = $('power-select-' + powerId);
	powerDiv.classList.add('displayed');
	// Display the detail
	var power = this.getPower(powerId);
	dojo.place(this.format_block('jstpl_powerDetail', power), 'grid-detail', 'only');
},

selectPower: function(powerId) {
	// Create a dummy
	var dummy = dojo.place(this.format_block('jstpl_powerSelect', this.getPower(0)), $('power-select-selected') );
	dummy.id = 'selectPower-dummy';
	// Slide the real card to the position of the dummy
	var animation_id = this.slideToObject('power-select-' + powerId, 'selectPower-dummy');
	dojo.connect(animation_id, 'onEnd', dojo.hitch(this, function() {
		// Replace the dummy with the real card
		var powerDiv = dojo.place('power-select-' + powerId, 'selectPower-dummy', 'replace');
		powerDiv.style = '';
		powerDiv.classList.add('selected');
		// Show confirm button
		if (this.isCurrentPlayerActive() && dojo.query('.power-card.small.selected').length == this.gamedatas.fplayers.length) {
			this.addActionButton('buttonValidateSelection', _('Confirm'), 'onClickValidateSelection', null, false, 'blue');
		}
	}));
	animation_id.play();
},

unselectPower: function(powerId) {
	// Create a dummy
	var dummy = dojo.place(this.format_block('jstpl_powerSelect', this.getPower(0)), $('power-select-available'), 'first');
	dummy.id = 'unselectPower-dummy';
	// Slide the real card to the position of the dummy
	var animation_id = this.slideToObject('power-select-' + powerId, 'unselectPower-dummy');
	dojo.connect(animation_id, 'onEnd', dojo.hitch(this, function() {
		// Replace the dummy with the real card
		var powerDiv = dojo.place('power-select-' + powerId, 'unselectPower-dummy', 'replace');
		powerDiv.style = '';
		powerDiv.classList.remove('selected');
		// Remove confirm button
		if (this.isCurrentPlayerActive()) {
			this.removeActionButtons();
		}
	}));
	animation_id.play();
},

//////////////////////////////////////////////////
//////////////   Player's action   ///////////////
//////////////////////////////////////////////////


/*
 * onClickSelectPower:
 * 	triggered after a click on a power (for "fair division" process)
 */
onClickSelectPower: function(powerId) {
	var active = this.isCurrentPlayerActive();
	var powerDiv = $('power-select-' + powerId);
	if (!powerDiv.classList.contains('displayed')) {
		// First click always displays the power
		// Everyone can display
		this.displayPower(powerId);
	} else if (active && powerDiv.classList.contains('selected')) {
		// Active player may unselect
		this.unselectPower(powerId);
	} else if (active && dojo.query('.power-card.small.selected').length < this.gamedatas.fplayers.length) {
		// Active player may select
		this.selectPower(powerId);
	}
},


/*
 * onClickValidateSelection:
 * 	triggered after a click on a button to validate the selected powers
 */
onClickValidateSelection: function() {
	// Check that this action is possible at this moment
	if (!this.checkAction('dividePowers')) {
		return false;
	}

	var ids = dojo.query('.power-card.small.selected').map(function(node) {
		return node.dataset.power;
	}).join(',');
	this.ajaxcall( "/santorini/santorini/dividePowers.html", { ids : ids }, this, res => {} );
},


/*
 * onClickChoosePower:
 * 	triggered after a click on a card for choosing a power (in the Fair Division process)
 */
onClickChoosePower: function(powerId) {
	if(! this.checkAction( 'choosePower' ) )
		return false;

	this.ajaxcall( "/santorini/santorini/choosePower.html", { id : powerId }, this, res => {} );
},



/*
 * onClickPlaceWorker:
 * 	triggered after a click on a space to place new worker
 */
onClickPlaceWorker: function(space) {
	// Check that this action is possible at this moment
	if(! this.checkAction( 'placeWorker' ) )
		return false;

	this.clearPossible();
	space.workerId = this.worker.id;
	this.ajaxcall( "/santorini/santorini/placeWorker.html", space, this, res => {} );
},


//////////
// Work //
//////////

/*
 * onClickSelectWorker:
 * 	triggered after a click on a worker
 */
onClickSelectWorker: function(worker) {
	this.clearPossible();
	this._selectedWorker = worker;
	this.board.makeClickable(worker.works, this.onClickSpace.bind(this), this._action);
	this.board.highlightPiece(worker);
	if(this._selectableWorkers.length > 1)
		this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray');
},

/*
 * onClickCancelSelect:
 * 	triggered after a click on the action button "buttonReset".
 *  unselect the previously selected worker and make every worker selectable
 */
onClickCancelSelect: function(evt) {
	dojo.stopEvent(evt);
	this.clearPossible();
	this.board.makeClickable(this._selectableWorkers, this.onClickSelectWorker.bind(this), 'select');
},


/*
 * onClickSpace:
 * 	triggered after a click on a space to either move/build/...
 */
onClickSpace: function(space) {
	if(space.arg == null)
		return this.onClickSpaceArg(space, null);

	if(space.arg.length == 1)
		return this.onClickSpaceArg(space, space.arg[0]);

	return this.dialogChooseArg(space);
},


dialogChooseArg: function(space){
	var dial = new ebg.popindialog();
	dial.create( 'chooseArg' );
	dial.setTitle( _("Choose the building block") ); // TODO : might be other choices ?

	space.arg.forEach(arg => {
		var div = dojo.place(this.format_block('jstpl_argPrompt', { arg : arg }) , $('popin_chooseArg_contents'));
		dojo.connect(div, 'onclick', e => {
			dial.destroy();
			this.onClickSpaceArg(space, arg)
		});
	});
	dial.show();
},



onClickSpaceArg: function(space, arg) {
	if( !this.checkAction( this._action ) )
		return;

	this.ajaxcall( "/santorini/santorini/work.html", {
		workerId: this._selectedWorker.id,
		x: space.x,
		y: space.y,
		z: space.z,
		arg: arg,
	}, this, res => {} );
	this.clearPossible(); // Make sur to clear after sending ajax otherwise selectedWorker will be null
},



/*
 * onClickSkip:
 * 	TODO
 */
onClickSkip: function() {
	if( !this.checkAction( 'skip' ) )
		return;

	this.ajaxcall( "/santorini/santorini/skipWork.html", {}, this, res => {} );
	this.clearPossible();
},


///////////////////////////////////////////////////
//////   Reaction to cometD notifications   ///////
///////////////////////////////////////////////////
/*
 * setupNotifications:
 *  In this method, you associate each of your game notifications with your local method to handle it.
 *	Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" in the santorini.game.php file.
 */
setupNotifications: function() {
	dojo.subscribe( 'powerAdded', this, "notif_powerAdded" );
	this.notifqueue.setSynchronous('powerAdded', 1000);

	dojo.subscribe('workerPlaced', this, 'notif_workerPlaced');
	this.notifqueue.setSynchronous('workerPlaced', 1000);

	dojo.subscribe('workerMoved', this, 'notif_workerMoved');
	this.notifqueue.setSynchronous('workerMoved', 2000);

	dojo.subscribe('blockBuilt', this, 'notif_blockBuilt');
	this.notifqueue.setSynchronous('blockBuilt', 1000);

	// Happens with Apollo
	dojo.subscribe('workerSwitched', this, 'notif_workerSwitched');
	this.notifqueue.setSynchronous('workerSwitched', 2000);

	// Happens with Minotaur
	dojo.subscribe('workerPushed', this, 'notif_workerPushed');
	this.notifqueue.setSynchronous('workerPushed', 2000);
},


/*
 * notif_powerAdded:
 *   called whenever a player choose a power on a Fair Division process
 */
notif_powerAdded: function(n) {
	debug('Notif: a power was chosen', n.args);
	this.addPowerToPlayer(n.args.player_id, n.args.power_id);
},


/*
 * notif_workerPlaced:
 *   called whenever a new worker is placed on the board
 */
notif_workerPlaced: function(n) {
	debug('Notif: new worker placed', n.args);
	this.createPiece(n.args.piece);
},


/*
 * notif_workerMoved:
 *   called whenever a worker is moved on the board
 */
notif_workerMoved : function(n) {
	debug('Notif: worker moved', n.args);
	this.board.movePiece(n.args.piece, n.args.space);
},


/*
 * notif_blockBuilt:
 *   called whenever a new block is built
 */
notif_blockBuilt: function(n) {
	console.log('Notif: block built', n.args);
	this.createPiece(n.args.piece);
},



/*
 * notif_workerSwitched:
 *   called whenever two workers are switched using Apollo
 */
notif_workerSwitched : function(n) {
	console.info('Notif: worker switched', n.args);
	this.board.switchPiece(n.args.piece1, n.args.piece2);
},

/*
 * notif_workerPushed:
 *   called whenever a worker is pushed using Minotaur
 */
notif_workerPushed : function(n) {
	console.info('Notif: worker pushed', n.args);
	this.board.movePiece(n.args.piece2, n.args.space, 1500);
	this.board.movePiece(n.args.piece1, n.args.piece2);
},

});
});
