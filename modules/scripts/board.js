import * as THREE from './three.js';
import { OrbitControls } from './OrbitControls.min.js';
import { MeshManager } from './meshManager.js';
import { Tween, Ease } from './tweenjs.min.js';

function isMobile() {
	return dojo.hasClass('ebd-body', 'mobile_version');
}

//window['$'] = function(id){ return document.getElementById(id); };
const canvasHeight = () => (Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0, $('scene-container').getBoundingClientRect()['height']) - ($('3d-scene') ? dojo.style('3d-scene', 'marginTop') : 100));
const canvasWidth = () => document.getElementById("left-side").getBoundingClientRect()['width'];

// Zoom limits
var ZOOM_MIN = 15;
var ZOOM_MAX = 40;
var ZOOM_MAX_MOBILE = 50;

// Fall animation
const fallAnimation = {
	sky: 14,
	duration: 1000
};

const basicColor = 0xff0034;
const multiColor = 0x994d00;
const hoveringColor = 0x000000;
const highlightColor = 0x0012AA;

const lvlHeights = [0, 1.24, 2.44, 3.25];
const xCenters = { "-1": 6.1, 0: 4.2, 1: 2.12, 2: -0.04, 3: -2.12, 4: -4.2, "5": -5.2 };
const zCenters = { "-1": 6.1, 0: 4.15, 1: 2.13, 2: 0, 3: -2.12, 4: -4.2, "5": -5.2 };
const startPos = new THREE.Vector3(40, 24, 0);
const enterPos = new THREE.Vector3(20, 24, 0);


var Board = function (container, url) {
	console.info("Creating board");
	this._url = url;
	this._container = container;
	this._meshManager = new MeshManager(url);
	this._meshManager.load().then(() => {
		this.render();
		console.info("Meshes loaded, rendered scene should look good")
	});

	this._board = new Array();
	for (var i = -1; i <= 5; i++) {
		this._board[i] = new Array();
		for (var j = -1; j <= 5; j++) {
			this._board[i][j] = new Array();
			for (var k = 0; k < 4; k++) {
				this._board[i][j][k] = {
					piece: null,
					planeHover: null,
					onclick: null,
				};
			}
		}
	}
	this._ids = [];
	this._clickable = [];
	this._highlights = [];
	this._animations = [];
	this._animated = false;
	this._animateClickable = false;

	this.initScene();
	this.initBoard();
	this.updateSize();
};



/*
 * Init basic elements of THREE.js
 *  - scene
 *  - camera
 *  - lights
 *  - renderer
 *  - controls
 * for debug : stats, axes helper and grid
 */

Board.prototype.initScene = function () {
	// Scene
	this._scene = new THREE.Scene();
	//	this._scene.background = new THREE.Color(0x29a9e0);
	//	this._scene.background.convertLinearToGamma( 2 );

	// Camera
	this._camera = new THREE.PerspectiveCamera(30, canvasWidth() / canvasHeight(), 1, isMobile() ? 250 : 150);
	this._camera.position.copy(startPos);
	this._camera.lookAt(new THREE.Vector3(0, 0, 0));

	// Lights
	this._scene.add(new THREE.HemisphereLight(0xFFFFFF, 0xFFFFFF, 1));

	// Renderer
	this._renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, precision: "lowp", powerPreference: "high-performance" });
	this._renderer.setPixelRatio(window.devicePixelRatio);
	this._renderer.outputEncoding = THREE.sRGBEncoding;
	this._container.appendChild(this._renderer.domElement);
	this._renderer.domElement.id = '3d-scene';

	// Raycasting
	this._raycaster = new THREE.Raycaster();
	this._hoveringSpace = null;
	this._mouse = { x: 0, y: 0 };
	this._mouseDown = false;

	var playArea = document.getElementById('play-area');
	playArea.addEventListener('mousemove', (event) => {
		event.preventDefault();
		this._mouse = this.getRealMouseCoords(event.clientX, event.clientY);
		if (!this._mouseDown && this._clickable.length > 0) {
			this.raycasting(true);
		}
	}, false);
	playArea.addEventListener('mousedown', (event) => this._mouseDown = true);
	playArea.addEventListener('mouseup', (event) => this._mouseDown = false);

	this._enterScene = false;
};

Board.prototype.onLoad = function () {
	this._cameraAngle = { theta: 0 };
	var anim = Tween.get(this._cameraAngle, { loop: -1 }).to({ theta: 2 * Math.PI }, 60000, Ease.linear)
		.addEventListener('change', () => {
			if (this._enterScene)
				return;

			this._camera.position.x = Math.cos(this._cameraAngle.theta) * startPos.x;
			this._camera.position.y = startPos.y;
			this._camera.position.z = Math.sin(this._cameraAngle.theta) * startPos.x;
			this._camera.lookAt(new THREE.Vector3(0, 0, 0));
			this.render();
		});
};


Board.prototype.updateSize = function () {
	this._camera.aspect = canvasWidth() / canvasHeight();
	this._camera.updateProjectionMatrix();
	this._renderer.setSize(canvasWidth(), canvasHeight());
	this.render();
};


Board.prototype.getRealMouseCoords = function (px, py) {
	var rect = this._renderer.domElement.getBoundingClientRect();
	return {
		x: (px - rect.left) / rect.width * 2 - 1,
		y: -(py - rect.top) / rect.height * 2 + 1
	}
};


/*
 * enterScene : players start to play on the 3D board => stop animation and add controls
 */
Board.prototype.enterScene = function () {
	if (this._enterScene) {
		return;
	}
	this._enterScene = true;

	if (this._cameraAngle) {
		Tween.removeTweens(this._cameraAngle);
	}

	if ($('left-cloud')) {
		$('left-cloud').style.left = "-10vw";
		$('right-cloud').style.right = "-10vw";

		Tween.get(this._camera.position).to(enterPos, 2000).addEventListener('change', () => {
			this._camera.lookAt(new THREE.Vector3(0, 0, 0));
			this.render();
		});
	}

	// Controls
	var controls = new OrbitControls(this._camera, document.getElementById('play-area'));
	controls.enablePan = false;
	controls.maxPolarAngle = Math.PI * 0.45;
	controls.minDistance = ZOOM_MIN;
	controls.maxDistance = isMobile() ? ZOOM_MAX_MOBILE : ZOOM_MAX;
	controls.mouseButtons = {
		LEFT: THREE.MOUSE.ROTATE,
		RIGHT: THREE.MOUSE.ROTATE
	}
	controls.addEventListener('change', this.render.bind(this));
	controls.addEventListener('click', (event) => {
		this._mouse = this.getRealMouseCoords(event.posX, event.posY);
		this.raycasting(false);
	})
};



/*
 * Init the board game
 *  - sea
 *  - island
 *  - board (bottom and grass)
 *  - marks
 */
Board.prototype.initBoard = function () {
	var sea = this._meshManager.createMesh('sea');
	sea.rotation.set(0, Math.PI, 0);
	sea.position.set(0, -2.8, 0);
	this._scene.add(sea);

	var island = this._meshManager.createMesh('island');
	island.position.set(0, -1.6, 0);
	this._scene.add(island);

	var board = this._meshManager.createMesh('board');
	this._scene.add(board);

	var outerWall = this._meshManager.createMesh('outerWall');
	outerWall.position.set(0, -0.1, 0);
	this._scene.add(outerWall);

	var wall = this._meshManager.createMesh('innerWall');
	wall.position.set(0, -0.1, 0);
	this._scene.add(wall);

	this.initCoordsHelpers();
};


/*
 * Init the coords helpers
 */
Board.prototype.computeText = function (text, size) {
	size = size || 0.7;
	var canvas = document.createElement('canvas'),
		ctx = canvas.getContext('2d');

	canvas.width = 125;
	canvas.height = 125;
	ctx.font = "Bold 150px Arial";
	ctx.fillStyle = "rgba(24,52,24,0.15)";
	ctx.fillText(text, 15, 115);
	ctx.lineWidth = 5;
	ctx.strokeStyle = "rgba(24,52,24,0.25)";
	ctx.strokeText(text, 15, 115);

	var text = new THREE.Texture(canvas);
	text.needsUpdate = true;
	var textMesh = new THREE.Mesh(
		new THREE.PlaneGeometry(size, size),
		new THREE.MeshBasicMaterial({ map: text, side: THREE.DoubleSide, transparent: true })
	);
	textMesh.rotation.set(-Math.PI / 2, 0, Math.PI / 2);
	textMesh.layers.set(1);

	return textMesh;
};

Board.prototype.initCoordsHelpers = function () {
	// Cols
	var cols = ['A', 'B', 'C', 'D', 'E'];
	for (var i = 0; i < 5; i++) {
		var textMesh = this.computeText(cols[i])
		textMesh.position.set(xCenters[0] + 1.7, 0.01, zCenters[i]);
		this._scene.add(textMesh);
	}

	// Rows
	var rows = ['1', '2', '3', '4', '5'];
	for (var i = 0; i < 5; i++) {
		var textMesh = this.computeText(rows[i])
		textMesh.position.set(xCenters[i], 0.01, zCenters[0] + 1.6);
		this._scene.add(textMesh);
	}
};

Board.prototype.showCoordsHelpers = function () {
	this._camera.layers.enable(1);
};

Board.prototype.hideCoordsHelpers = function () {
	this._camera.layers.disable(1);
};

Board.prototype.toggleCoordsHelpers = function (b) {
	if (b) {
		this.showCoordsHelpers();
	} else {
		this.hideCoordsHelpers();
	}
	this.render();
};


/*
 * Render the scene
 */
Board.prototype.render = function () {
	this._renderer.render(this._scene, this._camera);
};


/*
 * Reset the board
 */
Board.prototype.reset = function () {
	this.clearClickable();
	this.clearHighlights();

	for (var i = 0; i < 5; i++) {
		for (var j = 0; j < 5; j++) {
			for (var k = 0; k < 4; k++) {
				if (this._board[i][j][k].piece != null) {
					this._scene.remove(this._board[i][j][k].piece)
					this._board[i][j][k].piece = null;
				}
			}
		}
	}

	this._ids = [];
};


/*
 * Diff current setup with new setup
 */
Board.prototype.diff = function (pieces) {
	this.clearClickable();
	this.clearHighlights();

	this._ids.slice().map((mesh, id) => {
		var piece = mesh.space;

		var space = pieces.reduce((carry, npiece) => npiece.id == id ? npiece : carry, null);
		// Still exist => move it if needed
		if (space != null) {
			if (piece.x != space.x || piece.y != space.y || piece.z != space.z) {
				if (this._board[piece.x][piece.y][piece.z].piece.pieceId == id) {
					this._board[piece.x][piece.y][piece.z].piece = null;
				}
				this.addMeshToBoard(mesh, space);
				this.moveMesh(mesh, space, 0, "none");
			}
		} else {
			// Remove it
			this._scene.remove(mesh);
			if (this._board[piece.x][piece.y][piece.z].piece.pieceId == id) {
				this._board[piece.x][piece.y][piece.z].piece = null;
			}
			delete this._ids[id];
		}
	});

	pieces.forEach(piece => {
		if (this._ids[piece.id] != undefined) {
			return;
		}
		this.addPiece(piece, "none");
	})
};

/*
 * Add a mesh to a given (abstract) position (useful for raycasting)
 */
Board.prototype.addMeshToBoard = function (mesh, space) {
	mesh.space = { x: space.x, y: space.y, z: space.z };
	this._board[space.x][space.y][space.z].piece = mesh;
}

/*
 * Add a piece to a given position
 * - mixed piece : contains the infos
 * - optionnal string animation : which kind of animation we want
 */
Board.prototype.addPiece = function (piece, animation) {
	animation = animation || "fall";
	var center = new THREE.Vector3(xCenters[piece.x], lvlHeights[piece.z], zCenters[piece.y]);
	var sky = center.clone();
	sky.setY(center.y + fallAnimation.sky);

	var mesh = this._meshManager.createMesh(piece.name || piece.type);
	mesh.name = piece.name;
	mesh.pieceId = piece.id;
	mesh.position.copy(animation == "fall" ? sky : center);
	mesh.material.opacity = (animation == "fall" || animation == "none") ? 1 : 0;
	var theta = piece.direction ? (-(piece.direction + 2.55) * Math.PI / 4) : ((Math.floor(Math.random() * 4) - 1) * Math.PI / 2);
	mesh.rotation.set(0, theta, 0);
	this._scene.add(mesh);
	this._ids[piece.id] = mesh;
	this.addMeshToBoard(mesh, piece);

	// If building => add text on layer 1
	if (['lvl0', 'lvl1', 'lvl2'].includes(piece.type)) {
		var textMesh = this.computeText(parseInt(piece.type[3]) + 1, 0.5);
		textMesh.position.set(0, lvlHeights[parseInt(piece.z) + 1] - lvlHeights[piece.z], 0);
		textMesh.rotation.set(-Math.PI / 2, 0, -mesh.rotation.y + Math.PI / 2);
		mesh.add(textMesh);
	}

	if (animation != "none") {
		return new Promise((resolve, reject) => {
			var tweenAnimation;
			if (animation == "fall") {
				tweenAnimation = Tween.get(mesh.position).to(center, fallAnimation.duration, Ease.quadInOut);
			} else if (animation == "fadeIn") {
				tweenAnimation = Tween.get(mesh.material).wait(400).to({ opacity: 1 }, 800, Ease.quadInOut);
			}
			tweenAnimation.call(resolve).addEventListener('change', () => this.render())
		});
	}
};


/*
 * Add a piece to a given position and move the mesh already here up
 * - mixed piece : contains the info
 */
Board.prototype.addPieceUnder = function (piece) {
	var space = { x: piece.x, y: piece.y, z: parseInt(piece.z) + 1 };
	this.movePiece(piece, space);
	this.addPiece(piece, "fadeIn");
};


/*
 * Move a mesh to a new position
 * - mixed mesh :
 * - mixed space : contains the location
 */
Board.prototype.moveMesh = function (mesh, space, delay, animation) {
	delay = delay || 0;
	animation = animation || "slide";

	// Animate
	var target = new THREE.Vector3(xCenters[space.x], lvlHeights[space.z], zCenters[space.y]);

	if (animation == "none") {
		mesh.position.copy(target);
		return;
	}

	var maxZ = Math.max(mesh.position.y, lvlHeights[space.z]) + 1;
	var tmp1 = mesh.position.clone();
	tmp1.setY(maxZ);
	var tmp2 = target.clone();
	tmp2.setY(maxZ);

	var theta = Math.atan2(target.x - mesh.position.x, target.z - mesh.position.z) + 3 * Math.PI / 2;

	Tween.get(mesh.rotation).wait(delay)
		.to({ y: theta }, 300, Ease.quadInOut)

	Tween.get(mesh.position).wait(delay)
		.to(tmp1, 600, Ease.quadInOut)
		.to(tmp2, 500, Ease.quadInOut)
		.to(target, 500, Ease.quadInOut)
		.addEventListener('change', () => this.render())
};


/*
 * Move a piece to a new position
 * - mixed pece : info about the piece
 * - mixed space : contains the location
 */
Board.prototype.movePiece = function (piece, space, delay, animation) {
	// Update location on (abstract) board
	var mesh = this._board[piece.x][piece.y][piece.z].piece;
	this._board[piece.x][piece.y][piece.z].piece = null;
	this.addMeshToBoard(mesh, space);

	this.moveMesh(mesh, space, delay, animation);
};

/*
 * Switch two pieces
 * - mixed piece1
 * - mixed piece2
 */
Board.prototype.switchPiece = function (piece1, piece2) {
	// Update location on (abstract) board
	var mesh1 = this._board[piece1.x][piece1.y][piece1.z].piece;
	var mesh2 = this._board[piece2.x][piece2.y][piece2.z].piece;
	this.addMeshToBoard(mesh1, piece2);
	this.addMeshToBoard(mesh2, piece1);

	this.moveMesh(mesh1, piece2);
	this.moveMesh(mesh2, piece1);
};


/*
 * Remove a piece
 * - mixed piece : contains the infos
 */
Board.prototype.removePiece = function (piece) {
	var mesh = this._board[piece.x][piece.y][piece.z].piece;
	this._board[piece.x][piece.y][piece.z].piece = null;
	delete this._ids[piece.id];

	return new Promise((resolve, reject) => {
		Tween.get(mesh.material).to({ opacity: 0 }, fallAnimation.duration, Ease.quadInOut)
			.call(() => { this._scene.remove(mesh); resolve() })
			.addEventListener('change', () => this.render())
	});
};




/*
 * Raycasting with two modes
 * - hover : change textures to reflect hovering
 * - click : use callback function on clicked object
 */
Board.prototype.raycasting = function (hover) {
	this._raycaster.setFromCamera(this._mouse, this._camera);
	var intersects = this._raycaster.intersectObjects(this._clickable);

	// Try to find the corresponding space (x,y,z)
	var space = (intersects.length > 0 && intersects[0].object.space) ? intersects[0].object.space : null;
	this._renderNeedUpdate = false;

	// Clear previous hovering if needed
	this.clearHovering(space);

	if (space !== null) {
		if (hover) {
			if (space != this._hoveringSpace) {
				this._renderNeedUpdate = true;
				this._hoveringSpace = space;
				var cell = this._board[space.x][space.y][space.z];
				this._originalHex = cell.planeHover.children[0].material.color.getHex();
				cell.planeHover.children[0].material.color.setHex(hoveringColor);
				if (cell.piece != null) {
					cell.piece.material.emissive.setHex(0x333333);
				}
				document.body.style.cursor = "pointer";
			}
		} else {
			// Enforce clearing of hovering
			this.clearHovering();
			this._board[space.x][space.y][space.z].onclick();
		}
	}

	if (!this._animateClickable && this._renderNeedUpdate) {
		this.render();
	}
};

/*
 * Clear hovering effect
 *  - optional argument space : no clearing if new space to hover is the same
 */
Board.prototype.clearHovering = function (space) {
	if (this._hoveringSpace === null || space == this._hoveringSpace) {
		return;
	}

	this._renderNeedUpdate = true;
	var cell = this._board[this._hoveringSpace.x][this._hoveringSpace.y][this._hoveringSpace.z];
	cell.planeHover.children[0].material.color.setHex(this._originalHex);
	if (cell.piece != null) {
		cell.piece.material.emissive.setHex(0x000000);
	}
	document.body.style.cursor = "default";
	this._hoveringSpace = null;
}

/*
 * Clear clickable mesh (useful after click)
 */
Board.prototype.clearClickable = function () {
	this._clickable.map((m) => {
		var cell = this._board[m.space.x][m.space.y][m.space.z];

		if (cell.planeHover !== null) {
			this._scene.remove(cell.planeHover)
		}

		cell.onclick = null;
	});

	this._clickable = [];
	if (!this._animateClickable) {
		this.render();
	}
};


/*
 * Make several spaces/pieces clickable to allow space selection (for placement/moving/building)
 */
Board.prototype.makeClickable = function (objects, callback, action) {
	objects.forEach(o => {
		// Store the callback into the board
		this._board[o.x][o.y][o.z].onclick = () => callback(o);

		// Add some interactive meshes to this space
		var center = new THREE.Vector3(xCenters[o.x], lvlHeights[o.z] + 0.01, zCenters[o.y]);

		// Transparent square to make the whole space interactive
		var mesh = new THREE.Mesh(
			new THREE.PlaneBufferGeometry(1.75, 1.75).rotateX(-Math.PI / 2),
			new THREE.MeshPhongMaterial({ opacity: 0, transparent: true })
		);
		mesh.position.copy(center);
		mesh.space = { x: o.x, y: o.y, z: o.z };
		this._scene.add(mesh);
		this._clickable.push(mesh);
		this._board[o.x][o.y][o.z].planeHover = mesh;

		// Create a marker depending on the action and whether there is a piece at this location
		var piece = this._board[o.x][o.y][o.z].piece;
		var mark = null;
		var color = (o.dialog || o.arg != null && o.arg.length > 1) ? multiColor : basicColor;
		if (piece !== null) {
			piece.space = mesh.space;
			this._clickable.push(piece);

			// Disk animation
			mark = new THREE.Mesh(
				new THREE.CircleGeometry(0.728, 32).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.7, transparent: true, side: THREE.DoubleSide, })
			);
		} else if (action == "playerBuild") {
			// Show square
			mark = new THREE.Mesh(
				new THREE.PlaneBufferGeometry(1.4, 1.4).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.5, transparent: true, side: THREE.DoubleSide, })
			);
		} else {
			// Ring animation
			mark = new THREE.Mesh(
				new THREE.RingGeometry(0.4, 0.53, 32).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.8, transparent: true, side: THREE.DoubleSide, })
			);
		}

		// Add the marker as a planeHover child
		mark.position.set(0, isMobile() ? 0.15 : 0.05, 0);
		mark.space = mesh.space;
		this._clickable.push(mark);
		mesh.add(mark);
	})

	this.render();
};



/*
 * Highlist piece
 * - mixed piece
 */
Board.prototype.highlightPiece = function (piece) {
	var center = new THREE.Vector3(xCenters[piece.x], lvlHeights[piece.z] + 0.05, zCenters[piece.y]);
	var mark = new THREE.Mesh(
		new THREE.CircleGeometry(0.8, 32).rotateX(-Math.PI / 2),
		new THREE.MeshPhongMaterial({ color: highlightColor, opacity: 0.7, transparent: true })
	);
	mark.position.copy(center);
	this._scene.add(mark);
	this._highlights.push(mark);
};

/*
 * Clear highlight pieces
 */
Board.prototype.clearHighlights = function () {
	this._highlights.map((m) => this._scene.remove(m));
	this._highlights = [];
};



window.Board = Board;
export { Board };
