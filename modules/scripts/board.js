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

const lvlHeights = [0, 1.24, 2.44, 3.18];
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
	this._hover = null;
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
				this.moveMesh(mesh, space, 0, "none");
			}
		} else {
			// Remove it
			this._scene.remove(mesh);
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
 * Add a piece to a given position
 * - mixed piece : contains the infos
 * - optionnal string animation : which kind of animation we want
 */
Board.prototype.addPiece = function (piece, animation) {
	animation = animation || "fall";
	var center = this.getCenter(piece);
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
	mesh.space = { x: piece.x, y: piece.y, z: piece.z };

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
Board.prototype.addPieceUnder = function (piece, under) {
	var space = { x: piece.x, y: piece.y, z: parseInt(piece.z) + 1 };
	this.movePiece(under, space);
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
	var target = this.getCenter(space);

	if (animation == "none") {
		mesh.position.copy(target);
		return;
	}

	var maxZ = Math.max(mesh.position.y, target.y) + 1;
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
	var mesh = this._ids[piece.id];
	mesh.space = { x: space.x, y: space.y, z: space.z };
	this.moveMesh(mesh, space, delay, animation);
};

/*
 * Switch two pieces
 * - mixed piece1
 * - mixed piece2
 */
Board.prototype.switchPiece = function (piece1, piece2) {
	// Update location on (abstract) board
	var mesh1 = this._ids[piece1.id];
	var mesh2 = this._ids[piece2.id];
	mesh1.space = { x: piece2.x, y: piece2.y, z: piece2.z };
	mesh2.space = { x: piece1.x, y: piece1.y, z: piece1.z };
	this.moveMesh(mesh1, piece2);
	this.moveMesh(mesh2, piece1);
};


/*
 * Remove a piece
 * - mixed piece : contains the infos
 */
Board.prototype.removePiece = function (piece) {
	var mesh = this._ids[piece.id];
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
	// Try to find the corresponding object
	this._raycaster.setFromCamera(this._mouse, this._camera);
	var intersects = this._raycaster.intersectObjects(this._clickable);
	var intersectObj = (intersects.length > 0 && intersects[0].object) ? intersects[0].object : null;

	// Clear previous hovering if needed
	this._renderNeedUpdate = false;
	this.clearHovering(intersectObj);

	if (intersectObj !== null) {
		// 3 meshes are clickable (transparent square, marker, piece)
		// Make sure we always operate on the transparent square
		if (intersectObj.clickableMesh != null) {
			intersectObj = intersectObj.clickableMesh;
		}
		if (hover) {
			if (intersectObj != this._hover) {
				this._renderNeedUpdate = true;
				this._hover = intersectObj;
				var marker = intersectObj.children[0];
				this._originalHex = marker.material.color.getHex();
				marker.material.color.setHex(hoveringColor);
				if (intersectObj.piece != null) {
					intersectObj.piece.material.emissive.setHex(0x333333);
				}
				document.body.style.cursor = "pointer";
			}
		} else {
			// Enforce clearing of hovering
			this.clearHovering();
			intersectObj.onclick();
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
Board.prototype.clearHovering = function (intersectObj) {
	if (this._hover == null || this._hover == intersectObj) {
		return;
	}

	this._renderNeedUpdate = true;
	var marker = this._hover.children[0];
	marker.material.color.setHex(this._originalHex);
	if (this._hover.piece != null) {
		this._hover.piece.material.emissive.setHex(0x000000);
	}
	document.body.style.cursor = "default";
	this._hover = null;
}

/*
 * Clear clickable mesh (useful after click)
 */
Board.prototype.clearClickable = function () {
	// 3 meshes are clickable (transparent square, marker, piece)
	this._clickable.map((m) => {
		if (m.clickableMesh != null) {
			// Disconnect piece from transparent square, but don't destroy
			m.clickableMesh == null;
		} else {
			// Destroy transparent square (which includes marker child)
			this._scene.remove(m)
		}
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
		// Add some interactive meshes to this space
		var center = this.getCenter(o, 0.01);

		// Transparent square to make the whole space interactive
		var transparent = new THREE.Mesh(
			new THREE.PlaneBufferGeometry(1.75, 1.75).rotateX(-Math.PI / 2),
			new THREE.MeshPhongMaterial({ opacity: 0, transparent: true })
		);
		transparent.name = "transparent";
		transparent.position.copy(center);
		transparent.space = { x: o.x, y: o.y, z: o.z };
		transparent.onclick = () => callback(o);
		this._scene.add(transparent);
		this._clickable.push(transparent);

		// Create a marker depending on the action and whether there is a piece at this location
		var piece = this._ids[o.id];
		var marker = null;
		var color = (o.dialog || o.arg != null && o.arg.length > 1) ? multiColor : basicColor;
		if (piece != null) {
			transparent.piece = piece;
			this._clickable.push(piece);
			piece.clickableMesh = transparent;

			// Circle unerneath selected worker
			marker = new THREE.Mesh(
				new THREE.CircleGeometry(0.728, 32).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.7, transparent: true, side: THREE.DoubleSide, })
			);
		} else if (action == "playerBuild") {
			// Square on space for build
			marker = new THREE.Mesh(
				new THREE.PlaneBufferGeometry(1.4, 1.4).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.5, transparent: true, side: THREE.DoubleSide, })
			);
		} else {
			// Ring on space for move
			marker = new THREE.Mesh(
				new THREE.RingGeometry(0.4, 0.53, 32).rotateX(-Math.PI / 2),
				new THREE.MeshPhysicalMaterial({ color: color, opacity: 0.8, transparent: true, side: THREE.DoubleSide, })
			);
		}

		// Add marker child to transparent square
		this._clickable.push(marker);
		marker.clickableMesh = transparent;
		marker.name = "marker";
		marker.position.set(0, isMobile() ? 0.15 : 0.05, 0);
		transparent.add(marker);
	})

	this.render();
};



/*
 * Highlist piece
 * - mixed piece
 */
Board.prototype.highlightPiece = function (piece) {
	var center = this.getCenter(piece, 0.05);
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

/*
 * Returns the vector for the center of this space (x, y, z).
 * The height is adjusted to include any tokens on this space plus the specified offset.
 * Take note of the order: Tokens must be added first!
 */
Board.prototype.getCenter = function (space, zOffset = 0) {
	var tokens = this._ids.filter((m) => m.space.x == space.x && m.space.y == space.y && m.space.z == space.z && m.name != null && m.name.startsWith("token")).length;
	var x = xCenters[space.x];
	var z = lvlHeights[space.z] + (tokens * 0.1) + zOffset;
	var y = zCenters[space.y];
	return new THREE.Vector3(x, z, y);
};


window.Board = Board;
export { Board };
