/**
 * @license TweenJS
 * Visit https://createjs.com for documentation, updates and examples.
 *
 * Copyright (c) 2017 gskinner.com, inc.
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

'use strict';


var Event =
function () {
  function Event(type, bubbles, cancelable) {
    if (bubbles === void 0) {
      bubbles = false;
    }
    if (cancelable === void 0) {
      cancelable = false;
    }
    this.type = type;
    this.target = null;
    this.currentTarget = null;
    this.eventPhase = 0;
    this.bubbles = bubbles;
    this.cancelable = cancelable;
    this.timeStamp = new Date().getTime();
    this.defaultPrevented = false;
    this.propagationStopped = false;
    this.immediatePropagationStopped = false;
    this.removed = false;
  }
  var _proto = Event.prototype;
  _proto.preventDefault = function preventDefault() {
    this.defaultPrevented = this.cancelable;
    return this;
  };
  _proto.stopPropagation = function stopPropagation() {
    this.propagationStopped = true;
    return this;
  };
  _proto.stopImmediatePropagation = function stopImmediatePropagation() {
    this.immediatePropagationStopped = this.propagationStopped = true;
    return this;
  };
  _proto.remove = function remove() {
    this.removed = true;
    return this;
  };
  _proto.clone = function clone() {
    var event = new Event(this.type, this.bubbles, this.cancelable);
    for (var n in this) {
      if (this.hasOwnProperty(n)) {
        event[n] = this[n];
      }
    }
    return event;
  };
  _proto.set = function set(props) {
    for (var n in props) {
      this[n] = props[n];
    }
    return this;
  };
  _proto.toString = function toString() {
    return "[" + this.constructor.name + " (type=" + this.type + ")]";
  };
  return Event;
}();

var EventDispatcher =
function () {
  EventDispatcher.initialize = function initialize(target) {
    var p = EventDispatcher.prototype;
    target.addEventListener = p.addEventListener;
    target.on = p.on;
    target.removeEventListener = target.off = p.removeEventListener;
    target.removeAllEventListeners = p.removeAllEventListeners;
    target.hasEventListener = p.hasEventListener;
    target.dispatchEvent = p.dispatchEvent;
    target._dispatchEvent = p._dispatchEvent;
    target.willTrigger = p.willTrigger;
  };
  function EventDispatcher() {
    this._listeners = null;
    this._captureListeners = null;
  }
  var _proto = EventDispatcher.prototype;
  _proto.addEventListener = function addEventListener(type, listener, useCapture) {
    if (useCapture === void 0) {
      useCapture = false;
    }
    var listeners;
    if (useCapture) {
      listeners = this._captureListeners = this._captureListeners || {};
    } else {
      listeners = this._listeners = this._listeners || {};
    }
    var arr = listeners[type];
    if (arr) {
      this.removeEventListener(type, listener, useCapture);
      arr = listeners[type];
    }
    if (arr) {
      arr.push(listener);
    } else {
      listeners[type] = [listener];
    }
    return listener;
  };
  _proto.on = function on(type, listener, scope, once, data, useCapture) {
    if (scope === void 0) {
      scope = null;
    }
    if (once === void 0) {
      once = false;
    }
    if (data === void 0) {
      data = {};
    }
    if (useCapture === void 0) {
      useCapture = false;
    }
    if (listener.handleEvent) {
      scope = scope || listener;
      listener = listener.handleEvent;
    }
    scope = scope || this;
    return this.addEventListener(type, function (evt) {
      listener.call(scope, evt, data);
      once && evt.remove();
    }, useCapture);
  };
  _proto.removeEventListener = function removeEventListener(type, listener, useCapture) {
    if (useCapture === void 0) {
      useCapture = false;
    }
    var listeners = useCapture ? this._captureListeners : this._listeners;
    if (!listeners) {
      return;
    }
    var arr = listeners[type];
    if (!arr) {
      return;
    }
    var l = arr.length;
    for (var i = 0; i < l; i++) {
      if (arr[i] === listener) {
        if (l === 1) {
          delete listeners[type];
        }
        else {
            arr.splice(i, 1);
          }
        break;
      }
    }
  };
  _proto.off = function off(type, listener, useCapture) {
    if (useCapture === void 0) {
      useCapture = false;
    }
    this.removeEventListener(type, listener, useCapture);
  };
  _proto.removeAllEventListeners = function removeAllEventListeners(type) {
    if (type === void 0) {
      type = null;
    }
    if (type) {
      if (this._listeners) {
        delete this._listeners[type];
      }
      if (this._captureListeners) {
        delete this._captureListeners[type];
      }
    } else {
      this._listeners = this._captureListeners = null;
    }
  };
  _proto.dispatchEvent = function dispatchEvent(eventObj, bubbles, cancelable) {
    if (bubbles === void 0) {
      bubbles = false;
    }
    if (cancelable === void 0) {
      cancelable = false;
    }
    if (typeof eventObj === "string") {
      var listeners = this._listeners;
      if (!bubbles && (!listeners || !listeners[eventObj])) {
        return true;
      }
      eventObj = new Event(eventObj, bubbles, cancelable);
    } else if (eventObj.target && eventObj.clone) {
      eventObj = eventObj.clone();
    }
    try {
      eventObj.target = this;
    } catch (e) {}
    if (!eventObj.bubbles || !this.parent) {
      this._dispatchEvent(eventObj, 2);
    } else {
      var top = this;
      var list = [top];
      while (top.parent) {
        list.push(top = top.parent);
      }
      var l = list.length;
      var i;
      for (i = l - 1; i >= 0 && !eventObj.propagationStopped; i--) {
        list[i]._dispatchEvent(eventObj, 1 + (i == 0));
      }
      for (i = 1; i < l && !eventObj.propagationStopped; i++) {
        list[i]._dispatchEvent(eventObj, 3);
      }
    }
    return !eventObj.defaultPrevented;
  };
  _proto.hasEventListener = function hasEventListener(type) {
    var listeners = this._listeners,
        captureListeners = this._captureListeners;
    return !!(listeners && listeners[type] || captureListeners && captureListeners[type]);
  };
  _proto.willTrigger = function willTrigger(type) {
    var o = this;
    while (o) {
      if (o.hasEventListener(type)) {
        return true;
      }
      o = o.parent;
    }
    return false;
  };
  _proto.toString = function toString() {
    return "[" + (this.constructor.name + this.name ? " " + this.name : "") + "]";
  };
  _proto._dispatchEvent = function _dispatchEvent(eventObj, eventPhase) {
    var listeners = eventPhase === 1 ? this._captureListeners : this._listeners;
    if (eventObj && listeners) {
      var arr = listeners[eventObj.type];
      var l;
      if (!arr || (l = arr.length) === 0) {
        return;
      }
      try {
        eventObj.currentTarget = this;
      } catch (e) {}
      try {
        eventObj.eventPhase = eventPhase;
      } catch (e) {}
      eventObj.removed = false;
      arr = arr.slice();
      for (var i = 0; i < l && !eventObj.immediatePropagationStopped; i++) {
        var o = arr[i];
        if (o.handleEvent) {
          o.handleEvent(eventObj);
        } else {
          o(eventObj);
        }
        if (eventObj.removed) {
          this.off(eventObj.type, o, eventPhase === 1);
          eventObj.removed = false;
        }
      }
    }
  };
  return EventDispatcher;
}();

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  subClass.__proto__ = superClass;
}

var Ticker =
function (_EventDispatcher) {
  _inheritsLoose(Ticker, _EventDispatcher);
  _createClass(Ticker, null, [{
    key: "RAF_SYNCHED",
    get: function get() {
      return "synched";
    }
  }, {
    key: "RAF",
    get: function get() {
      return "raf";
    }
  }, {
    key: "TIMEOUT",
    get: function get() {
      return "timeout";
    }
  }]);
  function Ticker(name) {
    var _this;
    _this = _EventDispatcher.call(this) || this;
    _this.name = name;
    _this.timingMode = Ticker.TIMEOUT;
    _this.maxDelta = 0;
    _this.paused = false;
    _this._inited = false;
    _this._startTime = 0;
    _this._pausedTime = 0;
    _this._ticks = 0;
    _this._pausedTicks = 0;
    _this._interval = 50;
    _this._lastTime = 0;
    _this._times = null;
    _this._tickTimes = null;
    _this._timerId = null;
    _this._raf = true;
    return _this;
  }
  var _proto = Ticker.prototype;
  _proto.init = function init() {
    if (this._inited) {
      return;
    }
    this._inited = true;
    this._times = [];
    this._tickTimes = [];
    this._startTime = this._getTime();
    this._times.push(this._lastTime = 0);
    this._setupTick();
  };
  _proto.reset = function reset() {
    if (this._raf) {
      var f = window.cancelAnimationFrame || window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame || window.oCancelAnimationFrame || window.msCancelAnimationFrame;
      f && f(this._timerId);
    } else {
      clearTimeout(this._timerId);
    }
    this.removeAllEventListeners("tick");
    this._timerId = this._times = this._tickTimes = null;
    this._startTime = this._lastTime = this._ticks = 0;
    this._inited = false;
  };
  _proto.addEventListener = function addEventListener(type, listener, useCapture) {
    !this._inited && this.init();
    return _EventDispatcher.prototype.addEventListener.call(this, type, listener, useCapture);
  };
  _proto.getMeasuredTickTime = function getMeasuredTickTime(ticks) {
    if (ticks === void 0) {
      ticks = null;
    }
    var times = this._tickTimes;
    if (!times || times.length < 1) {
      return -1;
    }
    ticks = Math.min(times.length, ticks || this.framerate | 0);
    return times.reduce(function (a, b) {
      return a + b;
    }, 0) / ticks;
  };
  _proto.getMeasuredFPS = function getMeasuredFPS(ticks) {
    if (ticks === void 0) {
      ticks = null;
    }
    var times = this._times;
    if (!times || times.length < 2) {
      return -1;
    }
    ticks = Math.min(times.length - 1, ticks || this.framerate | 0);
    return 1000 / ((times[0] - times[ticks]) / ticks);
  };
  _proto.getTime = function getTime(runTime) {
    if (runTime === void 0) {
      runTime = false;
    }
    return this._startTime ? this._getTime() - (runTime ? this._pausedTime : 0) : -1;
  };
  _proto.getEventTime = function getEventTime(runTime) {
    if (runTime === void 0) {
      runTime = false;
    }
    return this._startTime ? (this._lastTime || this._startTime) - (runTime ? this._pausedTime : 0) : -1;
  };
  _proto.getTicks = function getTicks(pauseable) {
    if (pauseable === void 0) {
      pauseable = false;
    }
    return this._ticks - (pauseable ? this._pausedTicks : 0);
  };
  _proto._handleSynch = function _handleSynch() {
    this._timerId = null;
    this._setupTick();
    if (this._getTime() - this._lastTime >= (this._interval - 1) * 0.97) {
      this._tick();
    }
  };
  _proto._handleRAF = function _handleRAF() {
    this._timerId = null;
    this._setupTick();
    this._tick();
  };
  _proto._handleTimeout = function _handleTimeout() {
    this._timerId = null;
    this._setupTick();
    this._tick();
  };
  _proto._setupTick = function _setupTick() {
    if (this._timerId != null) {
      return;
    }
    var mode = this.timingMode || this._raf && Ticker.RAF;
    if (mode === Ticker.RAF_SYNCHED || mode === Ticker.RAF) {
      var f = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame;
      if (f) {
        this._timerId = f(mode === Ticker.RAF ? this._handleRAF.bind(this) : this._handleSynch.bind(this));
        this._raf = true;
        return;
      }
    }
    this._raf = false;
    this._timerId = setTimeout(this._handleTimeout.bind(this), this._interval);
  };
  _proto._tick = function _tick() {
    var paused = this.paused,
        time = this._getTime(),
        elapsedTime = time - this._lastTime;
    this._lastTime = time;
    this._ticks++;
    if (paused) {
      this._pausedTicks++;
      this._pausedTime += elapsedTime;
    }
    if (this.hasEventListener("tick")) {
      var event = new Event("tick");
      var maxDelta = this.maxDelta;
      event.delta = maxDelta && elapsedTime > maxDelta ? maxDelta : elapsedTime;
      event.paused = paused;
      event.time = time;
      event.runTime = time - this._pausedTime;
      this.dispatchEvent(event);
    }
    this._tickTimes.unshift(this._getTime() - time);
    while (this._tickTimes.length > 100) {
      this._tickTimes.pop();
    }
    this._times.unshift(time);
    while (this._times.length > 100) {
      this._times.pop();
    }
  };
  _proto._getTime = function _getTime() {
    var now = window.performance && window.performance.now;
    return (now && now.call(performance) || new Date().getTime()) - this._startTime;
  };
  Ticker.on = function on(type, listener, scope, once, data, useCapture) {
    return _instance.on(type, listener, scope, once, data, useCapture);
  };
  Ticker.removeEventListener = function removeEventListener(type, listener, useCapture) {
    _instance.removeEventListener(type, listener, useCapture);
  };
  Ticker.off = function off(type, listener, useCapture) {
    _instance.off(type, listener, useCapture);
  };
  Ticker.removeAllEventListeners = function removeAllEventListeners(type) {
    _instance.removeAllEventListeners(type);
  };
  Ticker.dispatchEvent = function dispatchEvent(eventObj, bubbles, cancelable) {
    return _instance.dispatchEvent(eventObj, bubbles, cancelable);
  };
  Ticker.hasEventListener = function hasEventListener(type) {
    return _instance.hasEventListener(type);
  };
  Ticker.willTrigger = function willTrigger(type) {
    return _instance.willTrigger(type);
  };
  Ticker.toString = function toString() {
    return _instance.toString();
  };
  Ticker.init = function init() {
    _instance.init();
  };
  Ticker.reset = function reset() {
    _instance.reset();
  };
  Ticker.addEventListener = function addEventListener(type, listener, useCapture) {
    _instance.addEventListener(type, listener, useCapture);
  };
  Ticker.getMeasuredTickTime = function getMeasuredTickTime(ticks) {
    return _instance.getMeasuredTickTime(ticks);
  };
  Ticker.getMeasuredFPS = function getMeasuredFPS(ticks) {
    return _instance.getMeasuredFPS(ticks);
  };
  Ticker.getTime = function getTime(runTime) {
    return _instance.getTime(runTime);
  };
  Ticker.getEventTime = function getEventTime(runTime) {
    return _instance.getEventTime(runTime);
  };
  Ticker.getTicks = function getTicks(pauseable) {
    return _instance.getTicks(pauseable);
  };
  _createClass(Ticker, [{
    key: "interval",
    get: function get() {
      return this._interval;
    },
    set: function set(interval) {
      this._interval = interval;
      if (!this._inited) {
        return;
      }
      this._setupTick();
    }
  }, {
    key: "framerate",
    get: function get() {
      return 1000 / this._interval;
    },
    set: function set(framerate) {
      this.interval = 1000 / framerate;
    }
  }], [{
    key: "interval",
    get: function get() {
      return _instance.interval;
    },
    set: function set(interval) {
      _instance.interval = interval;
    }
  }, {
    key: "framerate",
    get: function get() {
      return _instance.framerate;
    },
    set: function set(framerate) {
      _instance.framerate = framerate;
    }
  }, {
    key: "name",
    get: function get() {
      return _instance.name;
    },
    set: function set(name) {
      _instance.name = name;
    }
  }, {
    key: "timingMode",
    get: function get() {
      return _instance.timingMode;
    },
    set: function set(timingMode) {
      _instance.timingMode = timingMode;
    }
  }, {
    key: "maxDelta",
    get: function get() {
      return _instance.maxDelta;
    },
    set: function set(maxDelta) {
      _instance.maxDelta = maxDelta;
    }
  }, {
    key: "paused",
    get: function get() {
      return _instance.paused;
    },
    set: function set(paused) {
      _instance.paused = paused;
    }
  }]);
  return Ticker;
}(EventDispatcher);
var _instance = new Ticker("createjs.global");

var AbstractTween =
function (_EventDispatcher) {
  _inheritsLoose(AbstractTween, _EventDispatcher);
  function AbstractTween(props) {
    var _this;
    _this = _EventDispatcher.call(this) || this;
    _this.ignoreGlobalPause = false;
    _this.loop = 0;
    _this.useTicks = false;
    _this.reversed = false;
    _this.bounce = false;
    _this.timeScale = 1;
    _this.duration = 0;
    _this.position = 0;
    _this.rawPosition = -1;
    _this._paused = true;
    _this._next = null;
    _this._prev = null;
    _this._parent = null;
    _this._labels = null;
    _this._labelList = null;
    if (props) {
      _this.useTicks = !!props.useTicks;
      _this.ignoreGlobalPause = !!props.ignoreGlobalPause;
      _this.loop = props.loop === true ? -1 : props.loop || 0;
      _this.reversed = !!props.reversed;
      _this.bounce = !!props.bounce;
      _this.timeScale = props.timeScale || 1;
      props.onChange && _this.addEventListener("change", props.onChange);
      props.onComplete && _this.addEventListener("complete", props.onComplete);
    }
    return _this;
  }
  var _proto = AbstractTween.prototype;
  _proto.advance = function advance(delta, ignoreActions) {
    if (ignoreActions === void 0) {
      ignoreActions = false;
    }
    this.setPosition(this.rawPosition + delta * this.timeScale, ignoreActions);
  };
  _proto.setPosition = function setPosition(rawPosition, ignoreActions, jump, callback) {
    if (ignoreActions === void 0) {
      ignoreActions = false;
    }
    if (jump === void 0) {
      jump = false;
    }
    var d = this.duration,
        loopCount = this.loop,
        prevRawPos = this.rawPosition;
    var loop = 0,
        t = 0,
        end = false;
    if (rawPosition < 0) {
      rawPosition = 0;
    }
    if (d === 0) {
      end = true;
      if (prevRawPos !== -1) {
        return end;
      }
    } else {
      loop = rawPosition / d | 0;
      t = rawPosition - loop * d;
      end = loopCount !== -1 && rawPosition >= loopCount * d + d;
      if (end) {
        rawPosition = (t = d) * (loop = loopCount) + d;
      }
      if (rawPosition === prevRawPos) {
        return end;
      }
      if (!this.reversed !== !(this.bounce && loop % 2)) {
        t = d - t;
      }
    }
    this.position = t;
    this.rawPosition = rawPosition;
    this._updatePosition(jump, end);
    if (end) {
      this.paused = true;
    }
    callback && callback(this);
    if (!ignoreActions) {
      this._runActions(prevRawPos, rawPosition, jump, !jump && prevRawPos === -1);
    }
    this.dispatchEvent("change");
    if (end) {
      this.dispatchEvent("complete");
    }
  };
  _proto.calculatePosition = function calculatePosition(rawPosition) {
    var d = this.duration,
        loopCount = this.loop;
    var loop = 0,
        t = 0;
    if (d === 0) {
      return 0;
    }
    if (loopCount !== -1 && rawPosition >= loopCount * d + d) {
      t = d;
      loop = loopCount;
    } else if (rawPosition < 0) {
      t = 0;
    } else {
      loop = rawPosition / d | 0;
      t = rawPosition - loop * d;
    }
    return !this.reversed !== !(this.bounce && loop % 2) ? d - t : t;
  };
  _proto.addLabel = function addLabel(label, position) {
    if (!this._labels) {
      this._labels = {};
    }
    this._labels[label] = position;
    var list = this._labelList;
    if (list) {
      for (var _i = 0, l = list.length; _i < l; _i++) {
        if (position < list[_i].position) {
          break;
        }
      }
      list.splice(i, 0, {
        label: label,
        position: position
      });
    }
  };
  _proto.gotoAndPlay = function gotoAndPlay(positionOrLabel) {
    this.paused = false;
    this._goto(positionOrLabel);
  };
  _proto.gotoAndStop = function gotoAndStop(positionOrLabel) {
    this.paused = true;
    this._goto(positionOrLabel);
  };
  _proto.resolve = function resolve(positionOrLabel) {
    var pos = Number(positionOrLabel);
    return isNaN(pos) ? this._labels && this._labels[positionOrLabel] : pos;
  };
  _proto.toString = function toString() {
    return "[" + this.constructor.name + (this.name ? " (name=" + this.name + ")" : "") + "]";
  };
  _proto.clone = function clone() {
    throw "AbstractTween cannot be cloned.";
  };
  _proto._init = function _init(props) {
    if (!props || !props.paused) {
      this.paused = false;
    }
    if (props && props.position != null) {
      this.setPosition(props.position);
    }
  };
  _proto._goto = function _goto(positionOrLabel) {
    var pos = this.resolve(positionOrLabel);
    if (pos != null) {
      this.setPosition(pos, false, true);
    }
  };
  _proto._runActions = function _runActions(startRawPos, endRawPos, jump, includeStart) {
    if (!this._actionHead && !this.tweens) {
      return;
    }
    var d = this.duration,
        loopCount = this.loop;
    var reversed = this.reversed,
        bounce = this.bounce;
    var loop0, loop1, t0, t1;
    if (d === 0) {
      loop0 = loop1 = t0 = t1 = 0;
      reversed = bounce = false;
    } else {
      loop0 = startRawPos / d | 0;
      loop1 = endRawPos / d | 0;
      t0 = startRawPos - loop0 * d;
      t1 = endRawPos - loop1 * d;
    }
    if (loopCount !== -1) {
      if (loop1 > loopCount) {
        t1 = d;
        loop1 = loopCount;
      }
      if (loop0 > loopCount) {
        t0 = d;
        loop0 = loopCount;
      }
    }
    if (jump) {
      return this._runActionsRange(t1, t1, jump, includeStart);
    }
    else if (loop0 === loop1 && t0 === t1 && !jump && !includeStart) {
        return;
      }
      else if (loop0 === -1) {
          loop0 = t0 = 0;
        }
    var dir = startRawPos <= endRawPos;
    var loop = loop0;
    do {
      var rev = !reversed !== !(bounce && loop % 2);
      var start = loop === loop0 ? t0 : dir ? 0 : d;
      var end = loop === loop1 ? t1 : dir ? d : 0;
      if (rev) {
        start = d - start;
        end = d - end;
      }
      if (bounce && loop !== loop0 && start === end) ; else if (this._runActionsRange(start, end, jump, includeStart || loop !== loop0 && !bounce)) {
        return true;
      }
      includeStart = false;
    } while (dir && ++loop <= loop1 || !dir && --loop >= loop1);
  };
  _proto._runActionsRange = function _runActionsRange(startPos, endPos, jump, includeStart) {
    throw "_runActionsRange is abstract and must be overridden by a subclass.";
  };
  _proto._updatePosition = function _updatePosition(jump, end) {
    throw "_updatePosition is abstract and must be overridden by a subclass.";
  };
  _createClass(AbstractTween, [{
    key: "labels",
    get: function get() {
      var list = this._labelList;
      if (!list) {
        list = this._labelList = [];
        var labels = this._labels;
        for (var label in labels) {
          list.push({
            label: label,
            position: labels[label]
          });
        }
        list.sort(function (a, b) {
          return a.position - b.position;
        });
      }
      return list;
    },
    set: function set(labels) {
      this._labels = labels;
      this._labelList = null;
    }
  }, {
    key: "currentLabel",
    get: function get() {
      var labels = this.labels;
      var pos = this.position;
      for (var _i2 = 0, l = labels.length; _i2 < l; _i2++) {
        if (pos < labels[_i2].position) {
          break;
        }
      }
      return i === 0 ? null : labels[i - 1].label;
    }
  }, {
    key: "paused",
    get: function get() {
      return this._paused;
    },
    set: function set(paused) {
      Tween._register(this, paused);
      this._paused = paused;
    }
  }]);
  return AbstractTween;
}(EventDispatcher);

function linear(t) {
  return t;
}
function get(amount) {
  if (amount < -1) {
    amount = -1;
  } else if (amount > 1) {
    amount = 1;
  }
  return function (t) {
    if (amount == 0) {
      return t;
    }
    if (amount < 0) {
      return t * (t * -amount + 1 + amount);
    }
    return t * ((2 - t) * amount + (1 - amount));
  };
}
function getPowIn(pow) {
  return function (t) {
    return Math.pow(t, pow);
  };
}
function getPowOut(pow) {
  return function (t) {
    return 1 - Math.pow(1 - t, pow);
  };
}
function getPowInOut(pow) {
  return function (t) {
    if ((t *= 2) < 1) return 0.5 * Math.pow(t, pow);
    return 1 - 0.5 * Math.abs(Math.pow(2 - t, pow));
  };
}
function sineIn(t) {
  return 1 - Math.cos(t * Math.PI / 2);
}
function sineOut(t) {
  return Math.sin(t * Math.PI / 2);
}
function sineInOut(t) {
  return -0.5 * (Math.cos(Math.PI * t) - 1);
}
function getBackIn(amount) {
  return function (t) {
    return t * t * ((amount + 1) * t - amount);
  };
}
function getBackOut(amount) {
  return function (t) {
    return --t * t * ((amount + 1) * t + amount) + 1;
  };
}
function getBackInOut(amount) {
  amount *= 1.525;
  return function (t) {
    if ((t *= 2) < 1) return 0.5 * (t * t * ((amount + 1) * t - amount));
    return 0.5 * ((t -= 2) * t * ((amount + 1) * t + amount) + 2);
  };
}
function circIn(t) {
  return -(Math.sqrt(1 - t * t) - 1);
}
function circOut(t) {
  return Math.sqrt(1 - --t * t);
}
function circInOut(t) {
  if ((t *= 2) < 1) return -0.5 * (Math.sqrt(1 - t * t) - 1);
  return 0.5 * (Math.sqrt(1 - (t -= 2) * t) + 1);
}
function bounceIn(t) {
  return 1 - Ease.bounceOut(1 - t);
}
function bounceOut(t) {
  if (t < 1 / 2.75) {
    return 7.5625 * t * t;
  } else if (t < 2 / 2.75) {
    return 7.5625 * (t -= 1.5 / 2.75) * t + 0.75;
  } else if (t < 2.5 / 2.75) {
    return 7.5625 * (t -= 2.25 / 2.75) * t + 0.9375;
  } else {
    return 7.5625 * (t -= 2.625 / 2.75) * t + 0.984375;
  }
}
function bounceInOut(t) {
  if (t < 0.5) return Ease.bounceIn(t * 2) * 0.5;
  return Ease.bounceOut(t * 2 - 1) * 0.5 + 0.5;
}
function getElasticIn(amplitude, period) {
  var pi2 = Math.PI * 2;
  return function (t) {
    if (t === 0 || t === 1) return t;
    var s = period / pi2 * Math.asin(1 / amplitude);
    return -(amplitude * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - s) * pi2 / period));
  };
}
function getElasticOut(amplitude, period) {
  var pi2 = Math.PI * 2;
  return function (t) {
    if (t === 0 || t === 1) return t;
    var s = period / pi2 * Math.asin(1 / amplitude);
    return amplitude * Math.pow(2, -10 * t) * Math.sin((t - s) * pi2 / period) + 1;
  };
}
function getElasticInOut(amplitude, period) {
  var pi2 = Math.PI * 2;
  return function (t) {
    var s = period / pi2 * Math.asin(1 / amplitude);
    if ((t *= 2) < 1) return -0.5 * (amplitude * Math.pow(2, 10 * (t -= 1)) * Math.sin((t - s) * pi2 / period));
    return amplitude * Math.pow(2, -10 * (t -= 1)) * Math.sin((t - s) * pi2 / period) * 0.5 + 1;
  };
}
var none = linear;
var quadIn = getPowIn(2);
var quadOut = getPowOut(2);
var quadInOut = getPowInOut(2);
var cubicIn = getPowIn(3);
var cubicOut = getPowOut(3);
var cubicInOut = getPowInOut(3);
var quartIn = getPowIn(4);
var quartOut = getPowOut(4);
var quartInOut = getPowInOut(4);
var quintIn = getPowIn(5);
var quintOut = getPowOut(5);
var quintInOut = getPowInOut(5);
var backIn = getBackIn(1.7);
var backOut = getBackOut(1.7);
var backInOut = getBackInOut(1.7);
var elasticIn = getElasticIn(1, 0.3);
var elasticOut = getElasticOut(1, 0.3);
var elasticInOut = getElasticInOut(1, 0.3 * 1.5);

var Ease = /*#__PURE__*/Object.freeze({
	linear: linear,
	get: get,
	getPowIn: getPowIn,
	getPowOut: getPowOut,
	getPowInOut: getPowInOut,
	sineIn: sineIn,
	sineOut: sineOut,
	sineInOut: sineInOut,
	getBackIn: getBackIn,
	getBackOut: getBackOut,
	getBackInOut: getBackInOut,
	circIn: circIn,
	circOut: circOut,
	circInOut: circInOut,
	bounceIn: bounceIn,
	bounceOut: bounceOut,
	bounceInOut: bounceInOut,
	getElasticIn: getElasticIn,
	getElasticOut: getElasticOut,
	getElasticInOut: getElasticInOut,
	none: none,
	quadIn: quadIn,
	quadOut: quadOut,
	quadInOut: quadInOut,
	cubicIn: cubicIn,
	cubicOut: cubicOut,
	cubicInOut: cubicInOut,
	quartIn: quartIn,
	quartOut: quartOut,
	quartInOut: quartInOut,
	quintIn: quintIn,
	quintOut: quintOut,
	quintInOut: quintInOut,
	backIn: backIn,
	backOut: backOut,
	backInOut: backInOut,
	elasticIn: elasticIn,
	elasticOut: elasticOut,
	elasticInOut: elasticInOut
});

var Tween =
function (_AbstractTween) {
  _inheritsLoose(Tween, _AbstractTween);
  function Tween(target, props) {
    var _this;
    _this = _AbstractTween.call(this, props) || this;
    _this.pluginData = null;
    _this.target = target;
    _this.passive = false;
    _this._stepHead = new TweenStep(null, 0, 0, {}, null, true);
    _this._stepTail = _this._stepHead;
    _this._stepPosition = 0;
    _this._actionHead = null;
    _this._actionTail = null;
    _this._plugins = null;
    _this._pluginIds = null;
    _this._injected = null;
    if (props) {
      _this.pluginData = props.pluginData;
      if (props.override) {
        Tween.removeTweens(target);
      }
    }
    if (!_this.pluginData) {
      _this.pluginData = {};
    }
    _this._init(props);
    return _this;
  }
  Tween.get = function get$$1(target, props) {
    return new Tween(target, props);
  };
  Tween.tick = function tick(delta, paused) {
    var tween = Tween._tweenHead;
    while (tween) {
      var next = tween._next;
      if (paused && !tween.ignoreGlobalPause || tween._paused) ; else {
        tween.advance(tween.useTicks ? 1 : delta);
      }
      tween = next;
    }
  };
  Tween.handleEvent = function handleEvent(event) {
    if (event.type === "tick") {
      this.tick(event.delta, event.paused);
    }
  };
  Tween.removeTweens = function removeTweens(target) {
    if (!target.tweenjs_count) {
      return;
    }
    var tween = Tween._tweenHead;
    while (tween) {
      var next = tween._next;
      if (tween.target === target) {
        tween.paused = true;
      }
      tween = next;
    }
    target.tweenjs_count = 0;
  };
  Tween.removeAllTweens = function removeAllTweens() {
    var tween = Tween._tweenHead;
    while (tween) {
      var next = tween._next;
      tween._paused = true;
      tween.target && (tween.target.tweenjs_count = 0);
      tween._next = tween._prev = null;
      tween = next;
    }
    Tween._tweenHead = Tween._tweenTail = null;
  };
  Tween.hasActiveTweens = function hasActiveTweens(target) {
    if (target) {
      return !!target.tweenjs_count;
    }
    return !!Tween._tweenHead;
  };
  Tween.installPlugin = function installPlugin(plugin, props) {
    plugin.install(props);
    var priority = plugin.priority = plugin.priority || 0,
        arr = Tween._plugins = Tween._plugins || [];
    for (var _i = 0, l = arr.length; _i < l; _i++) {
      if (priority < arr[_i].priority) {
        break;
      }
    }
    arr.splice(i, 0, plugin);
  };
  Tween._register = function _register(tween, paused) {
    var target = tween.target;
    if (!paused && tween._paused) {
      if (target) {
        target.tweenjs_count = target.tweenjs_count ? target.tweenjs_count + 1 : 1;
      }
      var tail = Tween._tweenTail;
      if (!tail) {
        Tween._tweenHead = Tween._tweenTail = tween;
      } else {
        Tween._tweenTail = tail._next = tween;
        tween._prev = tail;
      }
      if (!Tween._inited) {
        Ticker.addEventListener("tick", Tween);
        Tween._inited = true;
      }
    } else if (paused && !tween._paused) {
      if (target) {
        target.tweenjs_count--;
      }
      var next = tween._next,
          prev = tween._prev;
      if (next) {
        next._prev = prev;
      } else {
        Tween._tweenTail = prev;
      }
      if (prev) {
        prev._next = next;
      } else {
        Tween._tweenHead = next;
      }
      tween._next = tween._prev = null;
    }
  };
  var _proto = Tween.prototype;
  _proto.wait = function wait(duration, passive) {
    if (passive === void 0) {
      passive = false;
    }
    if (duration > 0) {
      this._addStep(+duration, this._stepTail.props, null, passive);
    }
    return this;
  };
  _proto.to = function to(props, duration, ease) {
    if (duration === void 0) {
      duration = 0;
    }
    if (ease === void 0) {
      ease = linear;
    }
    if (duration < 0) {
      duration = 0;
    }
    var step = this._addStep(+duration, null, ease);
    this._appendProps(props, step);
    return this;
  };
  _proto.label = function label(name) {
    this.addLabel(name, this.duration);
    return this;
  };
  _proto.call = function call(callback, params, scope) {
    return this._addAction(scope || this.target, callback, params || [this]);
  };
  _proto.set = function set(props, target) {
    return this._addAction(target || this.target, this._set, [props]);
  };
  _proto.play = function play(tween) {
    return this._addAction(tween || this, this._set, [{
      paused: false
    }]);
  };
  _proto.pause = function pause(tween) {
    return this._addAction(tween || this, this._set, [{
      paused: false
    }]);
  };
  _proto.clone = function clone() {
    throw "Tween can not be cloned.";
  };
  _proto._addPlugin = function _addPlugin(plugin) {
    var ids = this._pluginIds || (this._pluginIds = {}),
        id = plugin.id;
    if (!id || ids[id]) {
      return;
    }
    ids[id] = true;
    var plugins = this._plugins || (this._plugins = []),
        priority = plugin.priority || 0;
    for (var _i2 = 0, l = plugins.length; _i2 < l; _i2++) {
      if (priority < plugins[_i2].priority) {
        plugins.splice(_i2, 0, plugin);
        return;
      }
    }
    plugins.push(plugin);
  };
  _proto._updatePosition = function _updatePosition(jump, end) {
    var step = this._stepHead.next,
        t = this.position,
        d = this.duration;
    if (this.target && step) {
      var stepNext = step.next;
      while (stepNext && stepNext.t <= t) {
        step = step.next;
        stepNext = step.next;
      }
      var ratio = end ? d === 0 ? 1 : t / d : (t - step.t) / step.d;
      this._updateTargetProps(step, ratio, end);
    }
    this._stepPosition = step ? t - step.t : 0;
  };
  _proto._updateTargetProps = function _updateTargetProps(step, ratio, end) {
    if (this.passive = !!step.passive) {
      return;
    }
    var v, v0, v1, ease;
    var p0 = step.prev.props;
    var p1 = step.props;
    if (ease = step.ease) {
      ratio = ease(ratio, 0, 1, 1);
    }
    var plugins = this._plugins;
    proploop: for (var n in p0) {
      v0 = p0[n];
      v1 = p1[n];
      if (v0 !== v1 && typeof v0 === "number") {
        v = v0 + (v1 - v0) * ratio;
      } else {
        v = ratio >= 1 ? v1 : v0;
      }
      if (plugins) {
        for (var _i3 = 0, l = plugins.length; _i3 < l; _i3++) {
          var value = plugins[_i3].change(this, step, n, v, ratio, end);
          if (value === Tween.IGNORE) {
            continue proploop;
          }
          if (value !== undefined) {
            v = value;
          }
        }
      }
      this.target[n] = v;
    }
  };
  _proto._runActionsRange = function _runActionsRange(startPos, endPos, jump, includeStart) {
    var rev = startPos > endPos;
    var action = rev ? this._actionTail : this._actionHead;
    var ePos = endPos,
        sPos = startPos;
    if (rev) {
      ePos = startPos;
      sPos = endPos;
    }
    var t = this.position;
    while (action) {
      var pos = action.t;
      if (pos === endPos || pos > sPos && pos < ePos || includeStart && pos === startPos) {
        action.funct.apply(action.scope, action.params);
        if (t !== this.position) {
          return true;
        }
      }
      action = rev ? action.prev : action.next;
    }
  };
  _proto._appendProps = function _appendProps(props, step, stepPlugins) {
    var initProps = this._stepHead.props,
        target = this.target,
        plugins = Tween._plugins;
    var n, i, value, initValue, inject;
    var oldStep = step.prev,
        oldProps = oldStep.props;
    var stepProps = step.props || (step.props = this._cloneProps(oldProps));
    var cleanProps = {};
    for (n in props) {
      if (!props.hasOwnProperty(n)) {
        continue;
      }
      cleanProps[n] = stepProps[n] = props[n];
      if (initProps[n] !== undefined) {
        continue;
      }
      initValue = undefined;
      if (plugins) {
        for (i = plugins.length - 1; i >= 0; i--) {
          value = plugins[i].init(this, n, initValue);
          if (value !== undefined) {
            initValue = value;
          }
          if (initValue === Tween.IGNORE) {
            (ignored = ignored || {})[n] = true;
            delete stepProps[n];
            delete cleanProps[n];
            break;
          }
        }
      }
      if (initValue !== Tween.IGNORE) {
        if (initValue === undefined) {
          initValue = target[n];
        }
        oldProps[n] = initValue === undefined ? null : initValue;
      }
    }
    for (n in cleanProps) {
      value = props[n];
      var o = void 0,
          prev = oldStep;
      while ((o = prev) && (prev = o.prev)) {
        if (prev.props === o.props) {
          continue;
        }
        if (prev.props[n] !== undefined) {
          break;
        }
        prev.props[n] = oldProps[n];
      }
    }
    if (stepPlugins && (plugins = this._plugins)) {
      for (i = plugins.length - 1; i >= 0; i--) {
        plugins[i].step(this, step, cleanProps);
      }
    }
    if (inject = this._injected) {
      this._injected = null;
      this._appendProps(inject, step, false);
    }
  };
  _proto._injectProp = function _injectProp(name, value) {
    var o = this._injected || (this._injected = {});
    o[name] = value;
  };
  _proto._addStep = function _addStep(duration, props, ease, passive) {
    if (passive === void 0) {
      passive = false;
    }
    var step = new TweenStep(this._stepTail, this.duration, duration, props, ease, passive);
    this.duration += duration;
    return this._stepTail = this._stepTail.next = step;
  };
  _proto._addAction = function _addAction(scope, funct, params) {
    var action = new TweenAction(this._actionTail, this.duration, scope, funct, params);
    if (this._actionTail) {
      this._actionTail.next = action;
    } else {
      this._actionHead = action;
    }
    this._actionTail = action;
    return this;
  };
  _proto._set = function _set$$1(props) {
    for (var n in props) {
      this[n] = props[n];
    }
  };
  _proto._cloneProps = function _cloneProps(props) {
    var o = {};
    for (var n in props) {
      o[n] = props[n];
    }
    return o;
  };
  return Tween;
}(AbstractTween);
{
  var p = Tween.prototype;
  p.w = p.wait;
  p.t = p.to;
  p.c = p.call;
  p.s = p.set;
}
Tween.IGNORE = {};
Tween._tweens = [];
Tween._plugins = null;
Tween._tweenHead = null;
Tween._tweenTail = null;
var TweenStep = function TweenStep(prev, t, d, props, ease, passive) {
  this.next = null;
  this.prev = prev;
  this.t = t;
  this.d = d;
  this.props = props;
  this.ease = ease;
  this.passive = passive;
  this.index = prev ? prev.index + 1 : 0;
};
var TweenAction = function TweenAction(prev, t, scope, funct, params) {
  this.next = null;
  this.d = 0;
  this.prev = prev;
  this.t = t;
  this.scope = scope;
  this.funct = funct;
  this.params = params;
};

var Timeline =
function (_AbstractTween) {
  _inheritsLoose(Timeline, _AbstractTween);
  function Timeline(props) {
    var _this;
    if (props === void 0) {
      props = {};
    }
    _this = _AbstractTween.call(this, props) || this;
    _this.tweens = [];
    if (props.tweens) {
      var _this2;
      (_this2 = _this).addTween.apply(_this2, props.tweens);
    }
    if (props.labels) {
      _this.labels = props.labels;
    }
    _this._init(props);
    return _this;
  }
  var _proto = Timeline.prototype;
  _proto.addTween = function addTween() {
    var l = arguments.length;
    if (l === 1) {
      var tween = arguments.length <= 0 ? undefined : arguments[0];
      this.tweens.push(tween);
      tween._parent = this;
      tween.paused = true;
      var d = tween.duration;
      if (tween.loop > 0) {
        d *= tween.loop + 1;
      }
      if (d > this.duration) {
        this.duration = d;
      }
      if (this.rawPosition >= 0) {
        tween.setPosition(this.rawPosition);
      }
      return tween;
    }
    if (l > 1) {
      for (var i = 0; i < l; i++) {
        this.addTween(i < 0 || arguments.length <= i ? undefined : arguments[i]);
      }
      return l - 1 < 0 || arguments.length <= l - 1 ? undefined : arguments[l - 1];
    }
    return null;
  };
  _proto.removeTween = function removeTween() {
    var l = arguments.length;
    if (l === 1) {
      var tw = this.tweens;
      var tween = arguments.length <= 0 ? undefined : arguments[0];
      var i = tw.length;
      while (i--) {
        if (tw[i] === tween) {
          tw.splice(i, 1);
          tween._parent = null;
          if (tween.duration >= this.duration) {
            this.updateDuration();
          }
          return true;
        }
      }
      return false;
    }
    if (l > 1) {
      var good = true;
      for (var _i = 0; _i < l; _i++) {
        good = good && this.removeTween(_i < 0 || arguments.length <= _i ? undefined : arguments[_i]);
      }
      return good;
    }
    return true;
  };
  _proto.updateDuration = function updateDuration() {
    this.duration = 0;
    for (var i = 0, l = this.tweens.length; i < l; i++) {
      var tween = this.tweens[i];
      var d = tween.duration;
      if (tween.loop > 0) {
        d *= tween.loop + 1;
      }
      if (d > this.duration) {
        this.duration = d;
      }
    }
  };
  _proto.clone = function clone() {
    throw "Timeline can not be cloned.";
  };
  _proto._updatePosition = function _updatePosition(jump, end) {
    var t = this.position;
    for (var i = 0, l = this.tweens.length; i < l; i++) {
      this.tweens[i].setPosition(t, true, jump);
    }
  };
  _proto._runActionsRange = function _runActionsRange(startPos, endPos, jump, includeStart) {
    var t = this.position;
    for (var i = 0, l = this.tweens.length; i < l; i++) {
      this.tweens[i]._runActions(startPos, endPos, jump, includeStart);
      if (t !== this.position) {
        return true;
      }
    }
  };
  return Timeline;
}(AbstractTween);


var cjs = window.createjs = window.createjs || {};
var v = cjs.v = cjs.v || {};
v.tweenjs = "NEXT";

export { Ease, Event, EventDispatcher, Ticker, Tween, AbstractTween, Timeline };
