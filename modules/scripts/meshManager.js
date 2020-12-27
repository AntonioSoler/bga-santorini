import * as THREE from './three.js';
import { GLTFLoader } from './GLTFLoader.min.js';

const Meshes = [
	/* Board Components */
	{
		n: 'sea',
		s: 0.8,
		a: false,
	},
	{
		n: 'island',
		s: 6.5,
		a: false,
	},
	{
		n: 'board',
		t: 'island',
		s: 0.84,
		a: false,
	},
	{
		n: 'outerWall',
		t: 'island',
		s: 0.84,
		a: false,
	},
	{
		n: 'innerWall',
		t: 'island',
		s: 0.84,
		a: false,
	},

	/* Buildings */
	{
		n: 'lvl0',
		s: 0.32,
		l: 0x89968a,
	},
	{
		n: 'lvl1',
		s: 0.32,
		l: 0x89968a,
	},
	{
		n: 'lvl2',
		s: 0.32,
		l: 0x89968a,
	},
	{
		n: 'lvl3',
		s: 0.32,
	},


	/* Workers */
	{
		n: 'f0worker',
		g: 'fWorker',
		s: 0.9,
	},
	{
		n: 'm0worker',
		g: 'mWorker',
		s: 0.9
	},
	{
		n: 'f1worker',
		g: 'fWorker',
		s: 0.9
	},
	{
		n: 'm1worker',
		g: 'mWorker',
		s: 0.9
	},
	{
		n: 'f2worker',
		g: 'fWorker',
		s: 0.9
	},
	{
		n: 'm2worker',
		g: 'mWorker',
		s: 0.9
	},
	{
		n: 'ram',
		s: 0.42,
	},

	/* Tokens */
	{
		n: 'tokenAbyss',
		g: 'token',
		s: 0.8,
	},
	{
		n: 'tokenWhirpool',
		g: 'token',
		s: 0.8,
	},
	{
		n: 'tokenTalus',
		g: 'token',
		s: 0.9,
	},
	{
		n: 'tokenCoin',
		g: 'token',
		s: 0.9,
	},
	{
		n: 'tokenWind',
		g: 'token8',
		s: 0.4,
	},
	{
		n: 'tokenArrow',
		g: 'token8',
		s: 0.4,
	},
];


var MeshManager = function (url) {
	this._url = url || "./";
	this._geometries = [];
	this._textures = [];
	this._loaded = false;
	this._meshAddLines = [];
}


/*
 * Allow to load several geometries using promises
 */
MeshManager.prototype.loadGeometry = function (names, scales) {
	var loader = new GLTFLoader();

	if (!(names instanceof Array))
		names = [names];

	for (var i = 0; i < names.length; i++)
		this._geometries[names[i]] = new THREE.BufferGeometry();

	var rnames = names.filter(n => n.indexOf("Line") == -1);
	return new Promise((resolve, reject) => {
		// Create a promise with all loading requests
		Promise.all(rnames.map((n) => loader.load(this._url + 'img/geometries/' + n + '.glb')))
			.then((values) => {
				// Store them (assuming only one mesh inside the obj file
				for (var i = 0; i < rnames.length; i++) {
					values[i].scene.traverse(child => {
						if (child.isMesh)
							this._geometries[rnames[i]].copy(child.geometry);
					});
					this._geometries[rnames[i]].scale(scales[i], scales[i], scales[i]);

					if (this._geometries[rnames[i] + "Line"] != undefined) {
						this._geometries[rnames[i] + "Line"] = new THREE.EdgesGeometry(this._geometries[rnames[i]], 20);
					}
				}

				// Add the lines to already existing meshs
				this._meshAddLines.forEach(m => {
					var line = new THREE.LineSegments(this._geometries[m.g + "Line"], new THREE.LineBasicMaterial({ color: m.l }));
					m.m.add(line);
				})
				this._meshAddLines = [];

				this._loaded = true;
				resolve();
			})
			.catch((err) => {
				reject(err);
			});
	});
};


/*
 * Allow to load several textures using promises
 */
MeshManager.prototype.loadTexture = function (names, ext) {
	var scope = this;
	if (!(names instanceof Array))
		names = [names];

	return new Promise(function (resolve, reject) {
		const manager = new THREE.LoadingManager(() => resolve());
		const loader = new THREE.TextureLoader(manager);
		for (var i = 0; i < names.length; i++)
			scope._textures[names[i]] = loader.load(scope._url + 'img/' + names[i] + "." + ext[i]);
	});
};


/*
 * Load models geometries and textures (lvl, workers, ...)
 */

MeshManager.prototype.load = function () {
	var scope = this;
	var aGeometries = [];
	var aScales = [];
	var aTextures = [];
	var aTexturesExt = [];

	Meshes.forEach((m) => {
		var g = m.g || m.n,
			t = m.t || m.n;

		if (aTextures.includes(t) === false) {
			aTextures.push(t);
			aTexturesExt.push(m.tExt || 'jpg');
		}

		if (typeof g === "string") {
			if (aGeometries.includes(g) === false) {
				aGeometries.push(g);
				aScales.push(m.s || 1);
			}
			if (m.l)
				aGeometries.push(g + "Line");
		}
		else
			scope._geometries[m.n] = g;
	});

	return Promise.all([
		this.loadGeometry(aGeometries, aScales),
		this.loadTexture(aTextures, aTexturesExt)
	]);
};



/*
 * Create mesh
 */
MeshManager.prototype.createMesh = function (name) {
	for (var i = 0; i < Meshes.length; i++) {
		if (name == Meshes[i].n) {
			var m = Meshes[i];
			var t = this._textures[typeof m.t == "string" ? m.t : m.n];
			var g = this._geometries[typeof m.g == "string" ? m.g : m.n];

			var material = new THREE.MeshLambertMaterial({
				map: m.c ? null : t,
				color: m.c || 0xDDDDDD,
				transparent: (m.a == undefined) ? true : m.a,
				side: THREE.DoubleSide
			});
			var mesh = new THREE.Mesh(g, material);

			if (m.l) {
				if (this._loaded) {
					var line = new THREE.LineSegments(this._geometries[(m.g || m.n) + "Line"], new THREE.LineBasicMaterial({ color: m.l }));
					mesh.add(line);
				}
				else {
					this._meshAddLines.push({ g: m.g || m.n, l: m.l, m: mesh });
				}
			}

			return mesh;
		}
	}
	throw "Mesh not found";
}


export { MeshManager, Meshes };
