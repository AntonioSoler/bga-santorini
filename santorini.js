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
	this.hexWidth = 84;
	this.hexHeight = 71;
	this.tryTile = null;
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
	var	container = document.getElementById('sceneContainer');
	this.board = new Board(container, URL);

		// TODO remove ?
		/*
		for (var player_id in gamedatas.players) {
			var player = gamedatas.players[player_id];
			player.colorName = colorNames[player.color];
			//dojo.place(this.format_block('jstpl_player_board', player), 'player_board_' + player_id);
			//this.updatePlayerCounters(player);
		}*/

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

	if(!this.isCurrentPlayerActive())
		return;

	this.clearPossible();

	// Place a worker
	if (stateName == 'playerPlaceWorker') {
		// TODO possible to be true ?
		if (args.args.accessibleSpaces.length == 0)
			throw new Error("No available spaces to place worker");

		this.worker = args.args.worker;
		this.board.makeClickable(args.args.accessibleSpaces, this.onClickPlaceWorker.bind(this));
	}
	// Move a worker
	else if(stateName == "playerMove"){
		this._movableWorkers = args.args.workers.filter(worker => worker.accessibleSpaces.length > 0);
		this.board.makeClickable(this._movableWorkers, this.onClickSelectWorker.bind(this));
	}
	// Build a block
	else if (stateName == 'playerBuild') {
		this.board.makeClickable(args.args.accessibleSpaces, this.onClickBuild.bind(this));
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
 * doAction:
 * 	TODO description ?
 * params :
 *  - action: TODO
 *  - args: TODO
 */
doAction: function(action, args) {
	if (this.checkAction(action)) {
		console.info('Taking action: ' + action, args);
		args = args || {};
		//args.lock = true; TODO remove ?

		this.ajaxcall('/santorini/santorini/' + action + '.html', args, this, function(result) {});
	}
},


/*
 * delayedExec:
 * 	TODO description ? remove ?
 */
delayedExec : function(onStart, onEnd, duration, delay) {
	duration = duration || 500;
	delay = delay || 0;

	if (this.instantaneousMode) {
		delay = Math.min(1, delay);
		duration = Math.min(1, duration);
	}

	var launch = () => {
		onStart();
		if (onEnd)
			setTimeout(onEnd, duration);
	};

	if (delay)	setTimeout(launch, delay);
	else				launch();
},


/*
 * createPiece:
 * 	TODO description ?
 * params:
 *  - piece: TODO
 *  - location: TODO
 */
createPiece: function(piece) {
	piece.name = piece.type;
	if(piece.type == "worker")
		piece.name = piece.type_arg + piece.type;

	this.board.addPiece(piece);
/*
	location = location || 'sky';

	if (piece.type.startsWith("worker")){
		var piecetype = "woman";
		if ( piece.type_arg == "1" ) { piecetype = "man"; };
		thispieceEL = dojo.place(this.format_block('jstpl_'+piecetype, {
		id: piece.id,
		color: piece.type,
		player: piece.location_arg
		}), location );
		} else {

	//TODO : random rotation
rand= Math.floor(Math.random() * 4);
angles = [0,90,180,270];
thispieceEL = dojo.place(this.format_block('jstpl_'+piece.type, {
id: piece.id,
angle: angles[rand]
}), location );
}

return thispieceEL;
*/
},



clearPossible: function() {
	this.removeActionButtons();
	this._selectedWorker = null;
	this.board.clearClickable();
},

//////////////////////////////////////////////////
//////////////   Player's action   ///////////////
//////////////////////////////////////////////////

onClickPlaceWorker: function(space) {
	// Check that this action is possible at this moment
	if(! this.checkAction( 'placeWorker' ) )
		return false;

	this.clearPossible();
	space.workerId = this.worker.id;
	this.ajaxcall( "/santorini/santorini/placeWorker.html", space, this, res => {} );
},

/////
// Tile actions
/////

onClickSelectWorker: function(worker) {
	this.clearPossible();
	this._selectedWorker = worker;
	console.log(this._selectedWorker);
	this.board.makeClickable(worker.accessibleSpaces, this.onClickMoveWorker.bind(this));
	this.addActionButton('buttonReset', _('Cancel'), 'onClickCancelSelect', null, false, 'gray');
},

onClickCancelSelect: function(evt) {
	dojo.stopEvent(evt);
	this.clearPossible();
	this.board.makeClickable(this._movableWorkers, this.onClickSelectWorker.bind(this));
},

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



/////
// Building actions
/////

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


});
});
