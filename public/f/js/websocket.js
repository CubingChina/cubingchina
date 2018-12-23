(function(global) {
  var supportWebSocket = 'WebSocket' in global;
  if (!supportWebSocket) {
    return;
  }
  var msgKey = 'websocket_msgs';
  var identifierKey = 'websockets_identifiers';
  function WS(uri) {
    this._msgs = store.get(msgKey) || [];
    this._eventHandlers = store.get(identifierKey) || {};
    this._identifiers = [];
    this.threshold = 20000;
    this.lastActiveTime = Date.now();
    this.uri = uri;
    this.connect();
    //heartbeat
    this.timer = setInterval(function() {
      //check connection
      if (this.conn.readyState == WebSocket.CLOSING || this.conn.readyState == WebSocket.CLOSED) {
        this.fire('disconnect');
        this.connect();
        return;
      }
      if (Date.now() - this.lastActiveTime > this.threshold) {
        this.send('ping');
      }
    }.bind(this), 1000)
    setInterval(function() {
      if (this.conn.readyState == WebSocket.OPEN) {
        for (var id in this._identifiers) {
          this.send(this._identifiers[id]);
        }
      }
    }.bind(this), 10000)
  }
  WS.prototype = {
    safeSend: function(msg, type, id) {
      id = type + '-' + id;
      this._identifiers[id] = msg;
      store.set(identifierKey, this._identifiers);
      this.send(msg);
    },
    send: function(msg) {
      if (this.conn.readyState != WebSocket.OPEN) {
        this._msgs.push(msg);
        store.set(msgKey, this._msgs);
        this.connect();
      } else {
        this.conn.send(JSON.stringify(msg));
        this.lastActiveTime = Date.now();
      }
      return this;
    },
    receive: function(e) {
      this.lastActiveTime = Date.now();
      var message = e.data;
      try {
        var message = JSON.parse(message);
        if (message === 'pong') {
          return;
        }
        if (message.code === 200) {
          var id = message.type + '-' + message.data.i;
          if (this._identifiers[id]) {
            delete this._identifiers[id];
          }
          this.fire(message.type, message.data, message);
        }
      } catch (e) {}
    },
    connect: function() {
      var that = this;
      if (that.conn instanceof WebSocket && that.conn.readyState == WebSocket.CONNECTING) {
        return;
      }
      try {
        var conn = that.conn = new WebSocket(that.uri);
        conn.onopen = function() {
          that.fire('connect');
          that._msgs.forEach(function(msg) {
            conn.send(JSON.stringify(msg));
          });
          that._msgs = [];
          store.set(msgKey, []);
          that.lastActiveTime = Date.now();
          conn.onmessage = that.receive.bind(that);
        }
      } catch (e) {}
    },
    on: function(event, callback) {
      this._eventHandlers[event] = this._eventHandlers[event] || [];
      this._eventHandlers[event].push(callback);
      return this;
    },
    off: function(event, callback) {
      if (!this._eventHandlers[event]) {
        return;
      }
      if (callback === undefined) {
        this._eventHandlers[event] = [];
        return;
      }
      var index = this._eventHandlers[event].indexOf(callback);
      if (index > -1) {
        this._eventHandlers[event].splice(index, 1);
      }
      return this;
    },
    fire: function(event, data, origin) {
      var that = this;
      if (that._eventHandlers['*']) {
        that._eventHandlers['*'].forEach(function(callback) {
          callback(data, origin);
        });
      }
      if (that._eventHandlers[event]) {
        that._eventHandlers[event].forEach(function(callback) {
          callback(data, origin);
        });
      }
      return that;
    }
  }
  global.WS = WS;
})(this);
