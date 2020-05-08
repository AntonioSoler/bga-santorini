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

define([
	"dojo", "dojo/_base/declare",
	"ebg/core/gamegui",
	"ebg/counter",
	"ebg/stock",
	"ebg/scrollmap"
], function(dojo, declare) {
	// Player colors
	const BLUE = "0000ff";
	const WHITE = "ffffff";

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
	console.info('SETUP', gamedatas);

	// Setup the board (3d scene using threejs)
	var	container = document.getElementById('scene-container');
	this.board = new Board(container, URL);

	gamedatas.fplayers.forEach(player => {
		return;
		// TODO remove or replace with name of power
		//var player = gamedatas.players[pId];
		//var player_board_div = $('player_board_' + pId);
		//dojo.place( this.format_block('jstpl_player_board', player ), player_board_div );

		// TODO : remove ?
		//		player.colorName = colorNames[player.color];
	});

	// Setup workers and buildings
	gamedatas.placedPieces.forEach(this.createPiece.bind(this));

	// TODO remove ?
	// Setup player boards
	colorNames = {
		'0000ff': 'blue',
		'ffffff': 'white'
	};

	// Setup game notifications
	this.setupNotifications();
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
	console.info('Entering state: ' + stateName, args.args);

	if(['powersDivide'].includes(stateName))
		this.focusContainer('powers');
	else
		this.focusContainer('board');

	if(!this.isCurrentPlayerActive())
		return;

	this.clearPossible();

	// Divide powers
	if (stateName == 'powersDivide') {
		this._selectedPowers = [];
		args.args.powers.forEach(power => {
			var div = dojo.place( this.format_block('jstpl_powerSelect', power ), $('grid-powers') );
			dojo.connect(div, 'onclick', e => this.onClickSelectPower(power) );
		})

	// Place a worker
	} else if (stateName == 'playerPlaceWorker') {
		// TODO possible to be true ?
		if (args.args.accessibleSpaces.length == 0)
			throw new Error("No available spaces to place worker");

		this.worker = args.args.worker;
		this.board.makeClickable(args.args.accessibleSpaces, this.onClickPlaceWorker.bind(this), 'place');
	}
	// Move a worker
	else if(stateName == "playerMove"){
		this._movableWorkers = args.args.workers.filter(worker => worker.accessibleSpaces.length > 0);
		this.board.makeClickable(this._movableWorkers, this.onClickSelectWorker.bind(this), 'select');
	}
	// Build a block
	else if (stateName == 'playerBuild') {
		this.board.makeClickable(args.args.accessibleSpaces, this.onClickBuild.bind(this), 'build');
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
	console.info('Leaving state: ' + stateName);
	this.clearPossible();
},



/*
 * onUpdateActionButtons:
 * 	TODO when is this called ?
 *  in this method you can manage "action buttons" that are displayed in the action status bar (ie: the HTML links in the status bar).
 */
onUpdateActionButtons: function(stateName, args) {
	console.info('Update action buttons: ' + stateName, args);

	// Make sure it the player's turn
	if (!this.isCurrentPlayerActive())
		return;
},




///////////////////////////////////////
////////    Utility methods    ////////
///////////////////////////////////////

/*
 * focusContainer:
 * 	show and hide containers depending on state
 */
focusContainer: function(container){
console.log(container);
	dojo.style( 'power-select-container', 'display', container == 'powers'? 'flex' : 'none');
	dojo.style( 'play-area',			 				'display', container == 'board'? 	'block' : 'none');

	if(container == "board")
		this.board.display();
	else
		this.board.hide();
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
	this._selectedWorker = null;
	this.board.clearClickable();
},


//////////////////////////////////////////////////
//////////////   Player's action   ///////////////
//////////////////////////////////////////////////


/*
 * onClickSelectPower:
 * 	triggered after a click on a power (for "fair division" process)
 */
onClickSelectPower: function(power) {
	var powerDiv = $('power-select-'+power.id);

	// If already selected, unselect it
	if(powerDiv.classList.contains('selected')){
		this._selectedPowers = this._selectedPowers.filter( p => p.id != power.id);
		powerDiv.classList.remove('selected');
		dojo.destroy('buttonValidateSelection');
		return;
	}

	// If already enough powers, don't do anything
	if(this._selectedPowers.length == this.gamedatas.fplayers.length)
		return;

	// Re-click on a already displayed power => select it
	if(	this._displayedPower == power){
		this.onClickAddPowerToSelection();
		return;
	}

	// Change color of selected power
	this._displayedPower = power
	dojo.query('.power-select').removeClass('displayed');
	powerDiv.classList.add('displayed');

	// Update details
	var detail = $('power-detail');
	dojo.query('#power-detail').removeClass().addClass('power-card power-'+power.id);
	$('power-detail-name').innerHTML = power.name;
	this.addTooltip( 'power-detail', power.text.join('\n'), '' );

	// Update action button
	this.removeActionButtons();
	this.addActionButton('buttonAddToSelection', _('Add to selection'), 'onClickAddPowerToSelection', null, false, 'gray');
},


/*
 * onClickAddPowerToSelection:
 * 	triggered after a click on a button to add selected power
 */
onClickAddPowerToSelection: function() {
	this._selectedPowers.push(this._displayedPower);
	dojo.query('.power-select.power-'+this._displayedPower.id).removeClass('displayed').addClass('selected');
	dojo.query('#power-detail').removeClass().addClass('power-card power-0');
	this._displayedPower = null;
	// TODO : remove banned

	this.removeActionButtons();
	if(this._selectedPowers.length == this.gamedatas.fplayers.length)
		this.addActionButton('buttonValidateSelection', _('Validate'), 'onClickValidateSelection', null, false, 'blue');
},


/*
 * onClickValidateSelection:
 * 	triggered after a click on a button to validate the selected powers
 */
onClickValidateSelection: function() {
	console.log(this._selectedPowers);
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
// Move //
//////////

/*
 * onClickSelectWorker:
 * 	triggered after a click on a worker
 */
onClickSelectWorker: function(worker) {
	this.clearPossible();
	this._selectedWorker = worker;
	console.log(this._selectedWorker);
	this.board.makeClickable(worker.accessibleSpaces, this.onClickMoveWorker.bind(this), 'move');
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
	this.board.makeClickable(this._movableWorkers, this.onClickSelectWorker.bind(this), 'select');
},

/*
 * onClickMoveWorker:
 * 	triggered after a click on a space to move the worker
 *  this shoud happen only if a worker is already selected (this._selectedWorker != null)
 */
onClickMoveWorker: function(space) {
	if( !this.checkAction( 'moveWorker' ) )
		return;

	this.ajaxcall( "/santorini/santorini/moveWorker.html", {
		workerId: this._selectedWorker.id,
		x: space.x,
		y: space.y,
		z: space.z
	}, this, res => {} );
	this.clearPossible(); // Make sur to clear after sending ajax otherwise selectedWorker will be null
},



///////////
// Build //
///////////

/*
 * onClickBuild:
 * 	triggered after a click on a space to build
 */
onClickBuild: function(space) {
	if( !this.checkAction( 'build' ) )
		return;

	this.clearPossible();
	this.ajaxcall( "/santorini/santorini/build.html", space, this, res => {} );
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
	dojo.subscribe('workerPlaced', this, 'notif_workerPlaced');
	this.notifqueue.setSynchronous('workerPlaced', 2000);

	dojo.subscribe('workerMoved', this, 'notif_workerMoved');
	this.notifqueue.setSynchronous('workerMoved', 2000);

	dojo.subscribe('blockBuilt', this, 'notif_blockBuilt');
	this.notifqueue.setSynchronous('blockBuilt', 2000);

	// Happens with Apollo
	dojo.subscribe('workerSwitched', this, 'notif_workerSwitched');
	this.notifqueue.setSynchronous('workerSwitched', 2000);

},


/*
 * notif_workerPlaced:
 *   called whenever a new worker is placed on the board
 */
notif_workerPlaced: function(n) {
	console.info('Notif: new worker placed', n.args);
	this.createPiece(n.args.piece);
},


/*
 * notif_workerMoved:
 *   called whenever a worker is moved on the board
 */
notif_workerMoved : function(n) {
	console.info('Notif: worker moved', n.args);
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

});
});
