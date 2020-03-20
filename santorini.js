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
        
        const HUT = 1;
        const TEMPLE = 2;
        const TOWER = 3;

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
                            var pieceEl = this.createPiece(thisPiece);
							targetEL = $('mapspace_'+thisSpace.x+'_'+thisSpace.y+'_'+thisSpace.z);
                            this.positionPiece (pieceEl, targetEL);
                            
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
                    if (stateName == 'playerMove') {
                        if (Object.keys(args.args.destinations_by_worker).length >= 1) {
                            // Auto-choose only option
                        
                        }
                    } else if (stateName == 'selectSpace') {
                        this.showPossibleSpaces();
                    } else if (stateName == 'building') {
                        this.showPossibleBuilding();

                    }
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function(stateName) {
                console.info('Leaving state: ' + stateName);
                if (stateName == 'tile' || stateName == 'building') {
                    this.clearPossible();
                    dojo.query(".tempbuilding").forEach(dojo.destroy);
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function(stateName, args) {
                console.info('Update action buttons: ' + stateName, args);
                if (this.isCurrentPlayerActive()) {
                    if (stateName == 'tile') {
                        if (this.tryTile) {
                            this.addActionButton('button_reset', _('Cancel'), 'onClickCancelTile', null, false, 'gray');
                            this.addActionButton('button_commit', _('Done'), 'onClickCommitTile');
                        }
                    } else if (stateName == 'building') {
                        this.addActionButton('button_reset', _('Cancel'), 'onClickCancelBuilding', null, false, 'gray');
                        this.addActionButton('button_commit', _('Done'), 'onClickCommitBuilding');
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
					while (elem.id != "playArea" ) { 
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

            updatePlayerCounters: function(player) {
                var player_id = player.player_id || player.id;
                $('count_huts_' + player_id).innerText = player.huts;
                $('count_temples_' + player_id).innerText = player.temples;
                $('count_towers_' + player_id).innerText = player.towers;
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

            createPiece: function(piece) {
				if ( piece.type.startsWith("worker")){
                var piecetype = "woman";
			    if ( piece.type_arg == "1" ) { piecetype = "man"; };
                thispieceEL = dojo.place(this.format_block('jstpl_'+piecetype, {
                    id: piece.id,
                    color: piece.type,
                    player: piece.location_arg
                }), 'sky');
                return thispieceEL;} 
            },

            positionPiece: function(pieceEl,destination) { 
			this.slideToObjectAbsolute(pieceEl, destination ,0,0, 1000, 0 ,function(){;} )
			//this.slideToObjectAbsolute(pieceEl, 'sky' ,0,0, 1000, 1 , dojo.hitch( this ,function(){ this.slideToObjectAbsolute(pieceEl, destination,0,0, 1000, 0 ,function(){;} )}));
            },

            positionTile: function(tileEl, coords) {
                tileEl.style.left = (coords.left - (this.hexWidth / 2)) + 'px';
                tileEl.style.top = coords.top + 'px';
            },

            removeTile: function(tileEl) {
                dojo.addClass(tileEl, 'fade-out');
            },

            getCoords: function(x, y) {
                var top = this.hexHeight * y - 70;
                var left = this.hexWidth * x - 35;
                if (y % 2 != 0) {
                    left += this.hexWidth / 2;
                }
                return {
                    top: top,
                    left: left,
                    style: 'top:' + top + 'px;left:' + left + 'px',
                };
            },

            clearPossible: function() {
                this.tryTile = null;
                this.tryBuilding = null;
                this.removeActionButtons();
                this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
                dojo.query('.possible').forEach(dojo.destroy);
                dojo.query('.tempbuilding').forEach(dojo.destroy);
            },

            showPossibleTile: function() {
                this.clearPossible();
                for (var i in this.gamedatas.gamestate.args.possible) {
                    var possible = this.gamedatas.gamestate.args.possible[i];
                    var coords = this.getCoords(possible.x, possible.y);
                    var possibleHtml = this.format_block('jstpl_possible', {
                        id: i,
                        z: possible.z - 1,
                        style: coords.style,
                        label: possible.z ,
                    });
                    var possibleEl = dojo.place(possibleHtml, 'map_scrollable_oversurface');
                }
                dojo.query('.face.possible').connect('onclick', this, 'onClickPossibleTile');
            },

            showPossibleSpaces: function() {
                this.clearPossible();
                for (var i in this.gamedatas.gamestate.args.spaces) {
                    var possible = this.gamedatas.gamestate.args.spaces[i];
                    var coords = this.getCoords(possible.x, possible.y);
                    var possibleHtml = this.format_block('jstpl_possible', {
                        id: i,
                        z: possible.z,
                        style: coords.style,
                        label: possible.z,
                    });
                    var possibleEl = dojo.place(possibleHtml, 'map_scrollable_oversurface');
                }
                dojo.query('.face.possible').connect('onclick', this, 'onClickPossibleSpaces');
            },

            showPossibleBuilding: function() {
                this.clearPossible();
                var options = this.gamedatas.gamestate.args.options;
                var tile_id = this.gamedatas.gamestate.args.tile_id;
                var subface = this.gamedatas.gamestate.args.subface;

                var possibleEl = dojo.place("<div id='buildPalette' class='palette possible'></div>", "hex_" + tile_id + "_" + subface);
                dojo.place("<div id='cancelator' style='transform:rotate(0deg)'><span class='facelabel'> ✗ </span></div>", 'buildPalette');

                var option_keys = Object.keys(options);
                if (option_keys.length == 1) {
                    var option_nbr = option_keys[0];
                    this.onClickPossibleBuilding(null, option_nbr);
                } else {
                    for (var option_nbr in options) {
                        var spaces = options[option_nbr];
                        var bldg_type = Math.floor(option_nbr / 10);
                        var possibleHtml = this.format_block('jstpl_building_' + bldg_type, {
                            colorName: 'tempbuilding'
                        });
                        if (bldg_type == HUT) {
                            var hutCount = spaces.reduce(function(sum, space) {
                                return sum + space.z;
                            }, 0);
                            possibleHtml += "<span class='facelabel'>" + hutCount + "</span>";
                        }
                        dojo.place("<div id='rota_" + option_nbr + "' class='rotator' style='transform:rotate(0deg)' >" + possibleHtml + "</div>", 'buildPalette');
                        dojo.query('#rota_' + option_nbr).connect('onclick', this, 'onClickPossibleBuilding');
                    }

                    for (var k = 0; k < $('buildPalette').children.length; k++) {
                        $('buildPalette').children[k].style.animation = "rotator" + (k + 1) + " 1.5s ease forwards 1";
                    }
                    dojo.query('#cancelator').connect('onclick', this, 'onClickCancelBuilding');
                }
            },

            ///////////////////////////////////////////////////
            //// Player's action

            /////
            // Tile actions
            /////

            onClickPossibleTile: function(evt, possible_nbr) {
                this.clearPossible();
                if (possible_nbr == null) {
                    dojo.stopEvent(evt);
                    var idParts = evt.currentTarget.id.split('_');
                    possible_nbr = idParts[1];
                }
                var possible = this.gamedatas.gamestate.args.possible[possible_nbr];
                var coords = this.getCoords(possible.x, possible.y);
                this.tryTile = {
                    tile_id: this.gamedatas.gamestate.args.tile_id,
                    tile_type: this.gamedatas.gamestate.args.tile_type,
                    x: possible.x,
                    y: possible.y,
                    z: possible.z,
                    r: possible.r[0],
                    possible: possible,
                };
                console.log('Trying tile ' + this.tryTile.tile_id + ' at [' + possible.x + ',' + possible.y + ',' + possible.z + ']');

                // Create tile
                var tileEl = this.createTile(this.tryTile);
                this.placeOnObject(tileEl.id, 'tile_p_' + this.player_id);
                this.positionTile(tileEl, coords);

                // Create rotator
                if (possible.r.length > 1) {
                    var rotatorHtml = this.format_block('jstpl_possible', {
                        id: 'rotator',
                        z: possible.z,
                        style: coords.style,
                        label: '↻',
                    });
                    var rotateEl = dojo.place(rotatorHtml, 'map_scrollable_oversurface');
                    dojo.connect(rotateEl, 'onclick', this, 'onClickRotateTile');
                }

                this.removeActionButtons();
                this.onUpdateActionButtons(this.gamedatas.gamestate.name, this.gamedatas.gamestate.args);
            },

            onClickRotateTile: function(evt) {
                dojo.stopEvent(evt);

                // Determine new rotation
                var rotations = this.tryTile.possible.r;
                var index = rotations.indexOf(this.tryTile.r);
                this.tryTile.r = rotations[(index + 1) % rotations.length];

                // Apply to tile
                var tileEl = $('tile_' + this.tryTile.tile_id);
                tileEl.style.transform = null;
                dojo.removeClass(tileEl, 'rotate0 rotate60 rotate120 rotate180 rotate240 rotate300');
                dojo.addClass(tileEl, 'rotate' + this.tryTile.r);
            },

            onClickCancelTile: function(evt) {
                dojo.stopEvent(evt);
                if (this.tryTile != null) {
                    var player_id = this.getActivePlayerId();
                    var tileEl = $('tile_' + this.tryTile.tile_id);
                    this.removeTile(tileEl);
                    this.showPossibleTile();
                }
            },

            onClickCommitTile: function(evt) {
                dojo.stopEvent(evt);
                if (this.tryTile == null) {
                    this.showMessage(_('You must place a tile.'), 'error');
                    return;
                }
                this.doAction('commitTile', this.tryTile);
            },

            /////
            // Building actions
            /////

            onClickPossibleSpaces: function(evt) {
                dojo.stopEvent(evt);
                this.clearPossible();

                var idParts = evt.currentTarget.id.split('_');
                var possible = this.gamedatas.gamestate.args.spaces[idParts[1]];
                console.log('Select space [' + possible.x + ',' + possible.y + ',' + possible.z + ']');
                this.doAction('selectSpace', {
                    x: possible.x,
                    y: possible.y,
                    z: possible.z,
                    tile_id: possible.tile_id,
                    subface: possible.subface
                })
            },

            onClickPossibleBuilding: function(evt, option_nbr) {
                this.clearPossible();
                if (option_nbr == null) {
                    dojo.stopEvent(evt);
                    var idParts = evt.currentTarget.id.split('_');
                    option_nbr = idParts[1];
                }
                this.tryBuilding = {
                    x: this.gamedatas.gamestate.args.x,
                    y: this.gamedatas.gamestate.args.y,
                    z: this.gamedatas.gamestate.args.z,
                    option_nbr: +option_nbr,
                };
                var bldg_type = Math.floor(option_nbr / 10);
                var spaces = this.gamedatas.gamestate.args.options[option_nbr];
                console.log('Trying building option ' + option_nbr + ' at [' + this.tryBuilding.x + ',' + this.tryBuilding.y + ',' + this.tryBuilding.z + ']');

                // Create temp buildings
                for (var b in spaces) {
                    var possible = spaces[b];
                    this.placeBuilding({
                        x: possible.x,
                        y: possible.y,
                        z: possible.z,
                        tile_id: possible.tile_id,
                        subface: possible.subface,
                        bldg_type: bldg_type,
                    });
                }
                dojo.query('.tempbuilding').connect('onclick', this, 'showPossibleBuilding');
            },

            onClickCancelBuilding: function(evt) {
                dojo.query(".tempbuilding").forEach(dojo.destroy);
                this.doAction("cancel");
            },

            onClickCommitBuilding: function(evt) {
                dojo.stopEvent(evt);
                if (this.tryBuilding == null) {
                    this.showMessage(_('You must place a building.'), 'error');
                    return;
                }
                this.doAction('commitBuilding', this.tryBuilding);
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
                dojo.subscribe('draw', this, 'notif_draw');
                dojo.subscribe('commitTile', this, 'notif_tile');
                dojo.subscribe('commitBuilding', this, 'notif_building');
            },

            notif_draw: function(n) {
                console.log('notif_draw', n.args);

                // Show preview tile
                var player_id = n.args.player_id;
                if (n.args.tile_type) {
                    var tileEl = this.createTile({
                        tile_id: 'p_' + player_id,
                        tile_type: n.args.tile_type
                    });
                    dojo.place(tileEl, 'preview_' + player_id, 'only');
                    dojo.removeClass('preview_' + player_id, 'unknown');
                } else {
                    $('preview_' + player_id).innerHTML = '';
                    dojo.addClass('preview_' + player_id, 'unknown');
                }

                // Update remaining tile counter
                if (n.args.remain != null) {
                    $('count_remain').innerText = n.args.remain;
                }
            },

            notif_tile: function(n) {
                console.log('notif_tile', n.args);
                var player_id = this.getActivePlayerId();
                var player = this.gamedatas.players[player_id];
                var colorClass = 'prior-move-' + player.colorName;

                // Create tile
                var tileEl = this.createTile(n.args);
                this.placeOnObject(tileEl, 'tile_p_' + player_id);
                dojo.query('.tile.' + colorClass).removeClass(colorClass)
                dojo.addClass(tileEl, colorClass);

                // Move into position
                var coords = this.getCoords(n.args.x, n.args.y);
                this.positionTile(tileEl, coords);

                // Destroy preview
                var previewEl = $('tile_p_' + player_id);
                this.removeTile(previewEl);
            },

            notif_building: function(n) {
                console.log('notif_building', n.args);
                this.updatePlayerCounters(n.args);
                for (var i in n.args.buildings) {
                    var building = n.args.buildings[i];
                    this.placeBuilding(building);
                }
            },

            notif_moveworker : function(notif) {
				var destination = notif.args.destination;
				this.slideToObjectAbsolute('card_'+notif.args.card_id, 'sky' ,0,0, 1000, 1 , dojo.hitch( this ,function(){ this.slideToObjectAbsolute('card_'+notif.args.card_id, destination,0,0, 1000 )}));
			},
        });
    });