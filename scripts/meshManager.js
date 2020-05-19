import * as THREE 				from './three.js';
import { GLTFLoader } 		from './GLTFLoader.min.js';

const Meshes = [
/* Board Components */
	{
		n:'sea',
		s:0.8,
		a:false,
	},
	{
		n:'island',
		s:6.5,
		a:false,
	},
	{
		n:'board',
		t:'island',
		s:0.84,
		a:false,
	},
	{
		n:'outerWall',
		t:'island',
		s:0.84,
		a:false,
	},
	{
		n:'innerWall',
		t:'island',
		s:0.84,
		a:false,
	},

/* Lvl */
	{
		n:'lvl0',
		s:0.32,
	},
	{
		n:'lvl1',
		s:0.32,
	},
	{
		n:'lvl2',
		s:0.32,
	},
	{
		n:'lvl3',
		s:0.32,
	},


/* Workers */
	{
		n:'f0worker',
		g:'fWorker',
		s:0.9
	},
	{
		n:'m0worker',
		g:'mWorker',
		s:0.9
	},
	{
		n:'f1worker',
		g:'fWorker',
		s:0.9
	},
	{
		n:'m1worker',
		g:'mWorker',
		s:0.9
	},
	{
		n:'f2worker',
		g:'fWorker',
		s:0.9
	},
	{
		n:'m2worker',
		g:'mWorker',
		s:0.9
	},
];


var MeshManager = function(url){
	this._url = url || "./";
	this._geometries = [];
	this._textures = [];
}


/*
 * Allow to load several geometries using promises
 */
MeshManager.prototype.loadGeometry = function(names, scales){
	var scope = this;
	var loader = new GLTFLoader();

	if(!(names instanceof Array))
		names = [names];

	for(var i = 0; i < names.length; i++)
		scope._geometries[names[i]] = new THREE.BufferGeometry();

	return new Promise(function(resolve, reject){
		// Create a promise with all loading requests
		Promise.all(names.map( (n) => loader.load(scope._url + 'geometries/' + n + '.glb') ))
		.then( (values) => {
			// Store them (assuming only one mesh inside the obj file
			for(var i = 0; i < names.length; i++){
				values[i].scene.traverse( child => {
					if(child.isMesh)
						scope._geometries[names[i]].copy(child.geometry);
				});
				scope._geometries[names[i]].scale(scales[i], scales[i], scales[i]);
			}

			resolve();
		})
		.catch( (err) => {
			reject(err);
		});
	});
};


/*
 * Allow to load several textures using promises
 */
MeshManager.prototype.loadTexture = function(names, ext){
	var scope = this;
	if(!(names instanceof Array))
		names = [names];

	return new Promise(function(resolve, reject){
		const manager = new THREE.LoadingManager(()=>resolve());
  	const loader = new THREE.TextureLoader(manager);
		for(var i = 0; i < names.length; i++)
			scope._textures[names[i]] = loader.load(scope._url + 'img/' + names[i] + "." + ext[i]);
	});
};


/*
 * Load models geometries and textures (lvl, workers, ...)
 */

MeshManager.prototype.load = function(){
	var scope = this;
	var aGeometries = [];
	var aScales = [];
	var aTextures = [];
	var aTexturesExt = [];

	Meshes.forEach((m) => {
		var g = m.g || m.n,
				t = m.t || m.n;

		if(aTextures.includes(t) === false){
			aTextures.push(t);
			aTexturesExt.push(m.tExt || 'jpg');
		}

		if(typeof g === "string"){
			if(aGeometries.includes(g) === false){
				aGeometries.push(g);
				aScales.push(m.s || 1);
			}
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
MeshManager.prototype.createMesh = function(name){
	for(var i = 0; i < Meshes.length; i++) {
	if(name == Meshes[i].n){
		var m = Meshes[i];
		var t = this._textures[typeof m.t == "string" ? m.t : m.n];
		var g = this._geometries[typeof m.g == "string"? m.g : m.n];

		var material = new THREE.MeshLambertMaterial({
			map : m.c? null : t,
			color: m.c || 0xDDDDDD,
			transparent: (m.a == undefined)? true : m.a,
			side: THREE.DoubleSide
		});
		var mesh = new THREE.Mesh(g, material);


		return mesh;
		}
	}
	throw "Mesh not found";
}


export { MeshManager, Meshes };
