/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * santorini implementation : (c) Morgalad
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
    ],
    function(dojo, declare) {
        // Plyer colors
        const BLUE = "0000ff";
        const WHITE = "ffffff";        

        // Zoom limits
        const ZOOM_MIN = 0.2;
        const ZOOM_MAX = 2;

        return declare("bgagame.santorini", ebg.core.gamegui, {
            constructor: function() {
                // Scrollable area
              
                this.hexWidth = 84;
                this.hexHeight = 71;
                this.tryTile = null;
                this.zoom = 1;

                if (!dojo.hasClass("ebd-body", "mode_3d")) {
                    dojo.addClass("ebd-body", "mode_3d");
                    //dojo.addClass("ebd-body", "enableTransitions");
                    $("globalaction_3d").innerHTML = "2D"; // controls the upper right button
                    this.control3dxaxis = 40; // rotation in degrees of x axis (it has a limit of 0 to 80 degrees in the frameword so users cannot turn it upsidedown)
                    this.control3dzaxis = 0; // rotation in degrees of z axis
                    this.control3dxpos = -100; // center of screen in pixels
                    this.control3dypos = -50; // center of screen in pixels
                    this.control3dscale = 0.8; // zoom level, 1 is default 2 is double normal size,
                    this.control3dmode3d = true; // is the 3d enabled
                    //    transform: rotateX(10deg) translate(-100px, -100px) rotateZ(0deg) scale3d(0.7, 0.7, 0.7);
                    $("game_play_area").style.transform = "rotatex(" + this.control3dxaxis + "deg) translate(" + this.control3dypos + "px," + this.control3dxpos + "px) rotateZ(" + this.control3dzaxis + "deg) scale3d(" + this.control3dscale + "," + this.control3dscale + "," + this.control3dscale + ")";
                }
            },

            /*
                setup:

                This method must set up the game user interface according to current game situation specified
                in parameters.

                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)

                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function(gamedatas) {
                console.info('SETUP', gamedatas);
                this.handles = [];
                // Setup 'fade-out' element destruction
                $('overall-content').addEventListener('animationend', function(e) {
                    if (e.animationName == 'fade-out') {
                        dojo.destroy(e.target);
                    }
                }, false);
                this.draggableElement3d($("pagesection_gameview"));

                // Setup remaining tile counter
               // dojo.place($('count_remain'), 'game_play_area_wrap', 'first');

                // Setup player boards
                colorNames = {
                    '0000ff': 'blue',
                    'ffffff': 'white'
                };
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];
                    player.colorName = colorNames[player.color];
                    //dojo.place(this.format_block('jstpl_player_board', player), 'player_board_' + player_id);
                    //this.updatePlayerCounters(player);
                    
                }

                // Setup scrollable map
                var mapContainer = $('map_container');
                mapContainer.onMouseDown = this.myonMouseDown;
                //this.scrollmap.create(mapContainer, $('map_scrollable'), $('map_surface'), $('map_scrollable_oversurface'));
                if (dojo.isFF) {
                    dojo.connect($('pagesection_gameview'), 'DOMMouseScroll', this, 'onMouseWheel');
                } else {
                    dojo.connect($('pagesection_gameview'), 'mousewheel', this, 'onMouseWheel');
                }

                // Setup tiles and buildings
                if ( gamedatas.spaces !== null ) {
                    
                    for (var s in gamedatas.spaces) {
                        var thisSpace = gamedatas.spaces[s];
						
                        if ( thisSpace.piece_id !== null ) {
                            thisPiece = gamedatas.placed_pieces[thisSpace.piece_id];
                            
							targetEL = $('mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z);
							var pieceEl = this.createPiece(thisPiece,targetEL);
                            //this.positionPiece (pieceEl, targetEL);                           
                        } 
                    }
                }

                // Setup game notifications
                this.setupNotifications();
            },

            change3d: function(_dc2, xpos, ypos, _dc3, _dc4, _dc5, _dc6) {
                this.control3dscale = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, this.control3dscale));
                if ((arguments[4] < 0 && this.control3dscale <= ZOOM_MIN) || (arguments[4] > 0 && this.control3dscale >= ZOOM_MAX)) {
                    arguments[4] = 0;
                }
                var isModeChange = arguments[5] === false;
                if (isModeChange) {
                    newMode3D = !this.control3dmode3d;
                    if (newMode3D) {
                        this.setZoom(1);
                        this.scrollmap.scrollToCenter();
                        this.scrollmap.disableScrolling();
                    } else {
                        this.setZoom(this.control3dscale);
                        this.scrollmap.enableScrolling();
                        this.scrollmap.scrollToCenter();
                    }
                }
                return this.inherited(arguments);
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function(stateName, args) {
                console.log('Entering state: ' + stateName, args.args);
                if (this.isCurrentPlayerActive()) {
					this.clearPossible();
                    if (stateName == 'playerMove') {
                        if (Object.keys(args.args.destinations_by_worker).length >= 1) {
							//this.destinations_by_worker = args.args.destinations_by_worker;
							this.activateworkers();
							
                        }
					}	
					if (stateName == 'playerPlaceWorker') {
                        if (Object.keys(args.args.accessible_spaces).length >= 1) {
							//this.destinations_by_worker = args.args.destinations_by_worker;
							for (var s in this.gamedatas.gamestate.args.accessible_spaces) {
								var thisSpace = this.gamedatas.gamestate.args.accessible_spaces[s];
								
								newtarget = dojo.place(this.format_block('jstpl_movetarget', {
									id: thisSpace.space_id,
									worker: 0						
									}), 'mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z );
								this.handles.push( dojo.connect(newtarget,'onclick', this, 'onClickPlaceTarget'));
							}
							
                        }	
                    } else if (stateName == 'selectSpace') {
                        this.showPossibleSpaces();
					
                    } else if (stateName == 'playerBuild') {
                        this.showPossibleBuilding();

                    }
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function(stateName) {
                console.info('Leaving state: ' + stateName);
                
                    this.clearPossible();
                
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function(stateName, args) {
                console.info('Update action buttons: ' + stateName, args);
                if (this.isCurrentPlayerActive()) {
                    if (stateName == 'playerMove') {
                            this.addActionButton('button_reset', _('Cancel'), 'onClickCancelMove', null, false, 'gray');
                            
                    }	
                }
            },

            onMouseWheel: function(evt) {
                dojo.stopEvent(evt);
                var d = Math.max(-1, Math.min(1, (evt.wheelDelta || -evt.detail))) * 0.1;
                this.change3d(0, 0, 0, 0, d, true, false);
            },

            myonMouseDown: function(evt) {
                if (!this.bEnableScrolling) {
                    return;
                }
                if (evt.which == 100) {
                    this.isdragging = true;
                    var _101c = dojo.position(this.scrollable_div);
                    var _101d = dojo.position(this.container_div);
                    this.dragging_offset_x = evt.pageX - (_101c.x - _101d.x);
                    this.dragging_offset_y = evt.pageY - (_101c.y - _101d.y);
                    this.dragging_handler = dojo.connect($("pagesection_gameview"), "onmousemove", this, "onMouseMove");
                    //this.dragging_handler_touch = dojo.connect($("ebd-body"), "ontouchmove", this, "onMouseMove");
                }
            },

            draggableElement3d: function(elmnt) {
                dojo.connect(elmnt, "onmousedown", this, "drag3dMouseDown");
                dojo.connect(elmnt, "onmouseup", this, "closeDragElement3d");

                elmnt.oncontextmenu = function() {
                    return false;
                }
                this.drag3d = elmnt;
            },

            drag3dMouseDown: function(e) {
                e = e || window.event;
                if (e.which == 3) {
                    dojo.stopEvent(e);
                    $("ebd-body").onmousemove = dojo.hitch(this, this.elementDrag3d);
                    $("pagesection_gameview").onmouseleave = dojo.hitch(this, this.closeDragElement3d);
                    dojo.addClass($("ebd-body"), "grabbinghand");
                }
            },

            elementDrag3d: function(e) {
				e = e || window.event;
				dojo.stopEvent(e);
				if (!this.isdragging) {
					this.isdragging=true;	
					var viewportOffset = e.currentTarget.getBoundingClientRect();
					//$("mouse_debug").style.display="block";
					//$("mouse_debug").innerHTML = "e.screenY:" + e.screenY + "<br> height: " + window.innerHeight + "<br> ofsettop: " + viewportOffset.top + "<br>E:"+ e.clientX + " " + e.clientY +"<br> "+ e.currentTarget.id ; 
					if ((e.screenY - viewportOffset.top) > (3 * window.innerHeight / 4)) {
						x = e.movementX;
					} else {
						x = -1 * e.movementX;
					}
					this.change3d(e.movementY / (-10), 0, 0, x / (-10), 0, true, false);
					this.isdragging=false;
				}	
            },

            closeDragElement3d: function(evt) {
                /* stop moving when mouse button is released:*/
                if (evt.which == 3) {
                    dojo.stopEvent(evt);
                    $("ebd-body").onmousemove = null;
                    dojo.removeClass($("ebd-body"), "grabbinghand");
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods
            doAction: function(action, args) {
                if (this.checkAction(action)) {
                    console.info('Taking action: ' + action, args);
                    args = args || {};
                    //args.lock = true;
                    this.ajaxcall('/santorini/santorini/' + action + '.html', args, this, function(result) {});
                }
            },
			
			delayedExec : function(onStart, onEnd, duration, delay) {
				if (typeof duration == "undefined") {
					duration = 500;
				}
				if (typeof delay == "undefined") {
					delay = 0;
				}
				if (this.instantaneousMode) {
					delay = Math.min(1, delay);
					duration = Math.min(1, duration);
				}
				if (delay) {
					setTimeout(function() {
						onStart();
						if (onEnd) {
							setTimeout(onEnd, duration);
						}
					}, delay);
				} else {
					onStart();
					if (onEnd) {
						setTimeout(onEnd, duration);
					}
				}
			},
			
			stripPosition : function(token) {
				// console.log(token + " STRIPPING");
				// remove any added positioning style
				dojo.style(token, "display", null);
				dojo.style(token, "top", null);
				dojo.style(token, "left", null);
				dojo.style(token, "position", null);
				dojo.style (token , { transform: "" });
			},
			stripTransition : function(token) {
				this.setTransition(token, "");
			},
			setTransition : function(token, value) {
				dojo.style(token, "transition", value);
				dojo.style(token, "-webkit-transition", value);
				dojo.style(token, "-moz-transition", value);
				dojo.style(token, "-o-transition", value);
			},
			resetPosition : function(token) {
				// console.log(token + " RESETING");
				// remove any added positioning style
				dojo.style(token, "display", null);
				dojo.style(token, "top", "0px");
				dojo.style(token, "left", "0px");
				dojo.style(token, "position", null);
				dojo.style(token, "transform", null);
			},
			
			getTransform :	function (elem) {
			var computedStyle = getComputedStyle(elem, null),
				val = computedStyle.transform ||
					computedStyle.webkitTransform ||
					computedStyle.MozTransform ||
					computedStyle.msTransform,
				matrix = this.parseMatrix(val),
				rotateY = Math.asin(-matrix.m13),
				rotateX, 
				rotateZ;
				position = computedStyle.position;
				rotateX = Math.atan2(matrix.m23, matrix.m33);
				rotateZ = Math.atan2(matrix.m12, matrix.m11);
			return {
				transformStyle: val,
				matrix: matrix,
				rotate: {
					x: rotateX,
					y: rotateY,
					z: rotateZ
				},
				translate: {
					x: matrix.m41,
					y: matrix.m42,
					z: matrix.m43
				},
				position: position
			};
		},


		/* Parses a matrix string and returns a 4x4 matrix
		---------------------------------------------------------------- */

		parseMatrix: function  (matrixString) {
			var c = matrixString.split(/\s*[(),]\s*/).slice(1,-1),
				matrix;

			if (c.length === 6) {
				// 'matrix()' (3x2)
				matrix = {
					m11: +c[0], m21: +c[2], m31: 0, m41: +c[4],
					m12: +c[1], m22: +c[3], m32: 0, m42: +c[5],
					m13: 0,     m23: 0,     m33: 1, m43: 0,
					m14: 0,     m24: 0,     m34: 0, m44: 1
				};
			} else if (c.length === 16) {
				// matrix3d() (4x4)
				matrix = {
					m11: +c[0], m21: +c[4], m31: +c[8], m41: +c[12],
					m12: +c[1], m22: +c[5], m32: +c[9], m42: +c[13],
					m13: +c[2], m23: +c[6], m33: +c[10], m43: +c[14],
					m14: +c[3], m24: +c[7], m34: +c[11], m44: +c[15]
				};

			} else {
				// handle 'none' or invalid values.
				matrix = {
					m11: 1, m21: 0, m31: 0, m41: 0,
					m12: 0, m22: 1, m32: 0, m42: 0,
					m13: 0, m23: 0, m33: 1, m43: 0,
					m14: 0, m24: 0, m34: 0, m44: 1
				};
			}
			return matrix;
		},

		/* Adds vector v2 to vector v1
		---------------------------------------------------------------- */

		addVectors: function (v1, v2) {
			return {
				x: v1.x + v2.x,
				y: v1.y + v2.y,
				z: v1.z + v2.z
			};
		},


		/* Rotates vector v1 around vector v2
		---------------------------------------------------------------- */

				rotateVector: function  (v1, v2) {
					var x1 = v1.x,
						y1 = v1.y,
						z1 = v1.z,
						angleX = v2.x / 2,
						angleY = v2.y / 2,
						angleZ = v2.z / 2,
						cr = Math.cos(angleX),
						cp = Math.cos(angleY),
						cy = Math.cos(angleZ),
						sr = Math.sin(angleX),
						sp = Math.sin(angleY),
						sy = Math.sin(angleZ),
						w = cr * cp * cy + -sr * sp * -sy,
						x = sr * cp * cy - -cr * sp * -sy,
						y = cr * sp * cy + sr * cp * sy,
						z = cr * cp * sy - -sr * sp * -cy,
						m0 = 1 - 2 * ( y * y + z * z ),
						m1 = 2 * (x * y + z * w),
						m2 = 2 * (x * z - y * w),
						m4 = 2 * ( x * y - z * w ),
						m5 = 1 - 2 * ( x * x + z * z ),
						m6 = 2 * (z * y + x * w ),
						m8 = 2 * ( x * z + y * w ),
						m9 = 2 * ( y * z - x * w ),
						m10 = 1 - 2 * ( x * x + y * y );
					return {
						x: x1 * m0 + y1 * m4 + z1 * m8,
						y: x1 * m1 + y1 * m5 + z1 * m9,
						z: x1 * m2 + y1 * m6 + z1 * m10
					};
				},
				
			
			computeVertexData: function (elem) {
				var w = elem.offsetWidth / 2,
					h = elem.offsetHeight / 2,
					v = {
						  a: { x: 0, y: 0, z: 0 }
					},
					transform;
				// Walk up the DOM and apply parent element transforms to each vertex
                //	while (elem.id != "overall-content" ) {
					while (elem.id != "map_surface" ) { 
					transform = this.getTransform(elem);
					v.a = this.addVectors( v.a , transform.translate );
					elem = elem.parentNode;		
				}
				return v;
				
			},
			
			attachToNewParentNoDestroy : function(mobile, new_parent) {
				if (mobile === null) {
					console.error("attachToNewParent: mobile obj is null");
					return;
				}
				if (new_parent === null) {
					console.error("attachToNewParent: new_parent is null");
					return;
				}
				if (typeof mobile == "string") {
					mobile = $(mobile);
				}
				if (typeof new_parent == "string") {
					new_parent = $(new_parent);
				}

				var src = dojo.position(mobile);
				dojo.style(mobile, "position", "absolute");
				dojo.place(mobile, new_parent, "last");
				return;
			},
			
			slideToObjectAbsolute : function(token, finalPlace, x, y, duration,delay,onEnd) {
				if (typeof token == 'string') {
					token = $(token);
				}
				if (typeof finalPlace == 'string') {
					finalPlace = $(finalPlace);
				}
				
				var self = this;
					
			    this.delayedExec(function() {
					self.stripTransition(token);
	
					origin=self.computeVertexData(token);
					destination=self.computeVertexData(finalPlace);	
					
					w = token.offsetWidth / 2;
					h = token.offsetHeight / 2;
					
					x += origin.a.x - destination.a.x;
					y += origin.a.y - destination.a.y;
					z = origin.a.z - destination.a.z;
					dojo.style (token , { transform: "translate3D("+ x +"px, "+ y +"px, "+ z +"px)" });
					self.setTransition(token, "all " + duration + "ms ease-in-out");
					self.attachToNewParentNoDestroy (token,finalPlace);
				
				}, function() {
					self.stripPosition(token);
					if (onEnd) {
						setTimeout(onEnd, duration);
					}
				}, duration, delay);				
			},

            setZoom: function(newZoom) {
                var newZoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, newZoom));
                if (this.zoom != newZoom) {
                    this.zoom = newZoom;
                    var zoomStyle = 'scale(' + this.zoom + ')';
                    var mapScrollable = $('map_scrollable');
                    var mapSurface = $('map_surface');
                    var mapOversurface = $('map_scrollable_oversurface');
                    mapScrollable.style.transform = zoomStyle;
                    mapSurface.style.transform = zoomStyle;
                    mapOversurface.style.transform = zoomStyle;
                }
            },

            createPiece: function(piece,location) {
				location = location || 'sky';
				if ( piece.type.startsWith("worker")){
					var piecetype = "woman";
					if ( piece.type_arg == "1" ) { piecetype = "man"; };
					thispieceEL = dojo.place(this.format_block('jstpl_'+piecetype, {
						id: piece.id,
						color: piece.type,
						player: piece.location_arg
					}), location );
                } else {
					rand= Math.floor(Math.random() * 4);					
					angles = [0,90,180,270];
					thispieceEL = dojo.place(this.format_block('jstpl_'+piece.type, {
						id: piece.id,
						angle: angles[rand]
					}), location );
				}
				
				return thispieceEL;
            },

            positionPiece: function(pieceEl,destination) { 
			this.slideToObjectAbsolute(pieceEl, destination ,0,0, 1000, 0 ,function(){;} )
			//this.slideToObjectAbsolute(pieceEl, 'sky' ,0,0, 1000, 1 , dojo.hitch( this ,function(){ this.slideToObjectAbsolute(pieceEl, destination,0,0, 1000, 0 ,function(){;} )}));
            },

            positionTile: function(tileEl, coords) {
                tileEl.style.left = (coords.left - (this.hexWidth / 2)) + 'px';
                tileEl.style.top = coords.top + 'px';
            },

            removeEl: function(tileEl) {
                dojo.addClass(tileEl, 'fade-out');
            },

            clearPossible: function() {
                this.removeActionButtons();
                //this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
                dojo.query('.movetarget').forEach(dojo.destroy);
                dojo.query('.buildtarget').forEach(dojo.destroy);
				dojo.forEach(this.handles, dojo.disconnect)
				dojo.query(".activeworker").removeClass("activeworker");
				this.handles = [];
            },

            activateworkers: function() {
                this.clearPossible();
                for (var w in this.gamedatas.gamestate.args.destinations_by_worker) {
                    var thisWorker = this.gamedatas.gamestate.args.destinations_by_worker[w];
                    dojo.addClass($("worker_"+w), "activeworker");
					this.handles.push( dojo.connect($("worker_"+w),'onclick', this, 'onClickPossibleworker'));
                }              
            },

            showPossibleBuilding: function() {
                this.clearPossible();
				for (var s in this.gamedatas.gamestate.args.neighbouring_spaces) {
                    var thisSpace = this.gamedatas.spaces[s];
                    newtarget = dojo.place(this.format_block('jstpl_buildtarget', {
						id: s												
						}), 'mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z );
					this.handles.push( dojo.connect(newtarget,'onclick', this, 'onClickBuildTarget'));
                }
                
            },

            ///////////////////////////////////////////////////
            //// Player's action

            /////
            // Tile actions
            /////

            onClickPossibleworker: function(evt, worker_id) {
                this.clearPossible();
                if (worker_id == null) {
                    dojo.stopEvent(evt);
                    var idParts = evt.currentTarget.id.split('_');
                    worker_id = idParts[1];
                }
                for (var s in this.gamedatas.gamestate.args.destinations_by_worker[worker_id]) {
                    var thisWorker = this.gamedatas.gamestate.args.destinations_by_worker[worker_id][s];
                    var thisSpace = thisWorker.space_id ;
					newtarget = dojo.place(this.format_block('jstpl_movetarget', {
						id: thisSpace,
						worker: worker_id						
						}), 'mapspace_'+thisWorker.x+'_'+thisWorker.y+'_'+thisWorker.z );
					this.handles.push( dojo.connect(newtarget,'onclick', this, 'onClickMoveTarget'));
				}
                
                this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
            },

            onClickCancelMove: function(evt) {
                dojo.stopEvent(evt);
				this.clearPossible();
				this.activateworkers();
               
            },

            onClickMoveTarget: function(evt) {
                dojo.stopEvent(evt);
				if( this.checkAction( 'move' ) )    // Check that this action is possible at this moment
				{           
					var idParts = evt.currentTarget.className.split(/[_ ]/);
					worker_id = idParts[1];
				
					var coords = evt.currentTarget.parentElement.id.split('_');
					x = coords[1];
					y = coords[2];
					z = coords[3];
					this.ajaxcall( "/santorini/santorini/move.html", {
						worker_id:worker_id,
						x:x,
						y:y,
						z:z
					}, this, function( result ) {} );
				}            
                this.clearPossible();
				this.removeActionButtons();
            },
			
			onClickPlaceTarget: function(evt) {
                dojo.stopEvent(evt);
				if( this.checkAction( 'place' ) )    // Check that this action is possible at this moment
				{           
					var idParts = evt.currentTarget.className.split(/[_ ]/);
					worker_id = idParts[1];
				
					var coords = evt.currentTarget.parentElement.id.split('_');
					x = coords[1];
					y = coords[2];
					z = coords[3];
					this.ajaxcall( "/santorini/santorini/place.html", {
						x:x,
						y:y,
						z:z
					}, this, function( result ) {} );
				}            
                this.clearPossible();
            },
			

            /////
            // Building actions
            /////

            onClickBuildTarget: function(evt) {
                dojo.stopEvent(evt);
				if( this.checkAction( 'build' ) )    // Check that this action is possible at this moment
				{           
					var idParts = evt.currentTarget.id.split(/[_ ]/);
					space_id = idParts[1];
				
					var coords = evt.currentTarget.parentElement.id.split('_');
					x = coords[1];
					y = coords[2];
					z = coords[3];
					this.ajaxcall( "/santorini/santorini/build.html", {
						x:x,
						y:y,
						z:z
					}, this, function( result ) {} );
				}            
                this.clearPossible();
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:

                In this method, you associate each of your game notifications with your local method to handle it.

                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your santorini.game.php file.

            */
            setupNotifications: function() {
                dojo.subscribe('blockBuilt', this, 'notif_building');
				this.notifqueue.setSynchronous('blockBuilt', 2000);
                dojo.subscribe('workerPlaced', this, 'notif_workerPlaced');
				this.notifqueue.setSynchronous('workerPlaced', 2000);
                dojo.subscribe('workerMoved', this, 'notif_moveworker');
				this.notifqueue.setSynchronous('workerMoved', 2000);
            },

            notif_workerPlaced: function(n) {
                console.log('notif_tile', n.args);
                var player_id = this.getActivePlayerId();
                var player = this.gamedatas.players[player_id];
				thisPiece = this.gamedatas.available_pieces[n.args.worker_id]
                var pieceEl = this.createPiece(thisPiece);
				thisSpace = this.gamedatas.spaces[n.args.space_id];
				targetEL = $('mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z);
                this.positionPiece (pieceEl, targetEL);
            },

            notif_building: function(n) {
                console.log('notif_building', n.args);
                var player_id = this.getActivePlayerId();
                var player = this.gamedatas.players[player_id];
				thisPiece = this.gamedatas.available_pieces[n.args.block.id]
                var pieceEl = this.createPiece(thisPiece);
				thisSpace = this.gamedatas.spaces[n.args.space_id];
				targetEL = $('mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z);
                this.positionPiece (pieceEl, targetEL);
            },

            notif_moveworker : function(notif) {
				thisSpace= this.gamedatas.spaces[notif.args.space_id];
				var destination = "mapspace_"+thisSpace.x+"_"+thisSpace.y+"_"+thisSpace.z;
				this.slideToObjectAbsolute('worker_'+notif.args.worker_id, 'sky' ,0,0, 800, 0 , dojo.hitch( this ,function(){ this.slideToObjectAbsolute('worker_'+notif.args.worker_id, destination,0,0, 800 )}));
			},
        });
    });