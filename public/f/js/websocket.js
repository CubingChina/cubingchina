(function(global) {
  var supportWebSocket = 'WebSocket' in global;
  if (!supportWebSocket) {
    return;
  }
  function WS(uri) {
    this._msgs = [];
    this._eventHandlers = [];
    this.threshold = 20000;
    this.lastActiveTime = Date.now();
    this.uri = uri;
    this.connect();
    //heartbeat
    this.timer = setInterval(function() {
      if (Date.now() - this.lastActiveTime > this.threshold) {
        this.send('ping');
      }
    }.bind(this), 1000)
  }
  WS.prototype = {
    send: function(msg) {
      if (this.conn.readyState != WebSocket.OPEN) {
        this._msgs.push(msg);
        this.connect();
      } else {
        this.conn.send(JSON.stringify(msg));
        this.lastActiveTime = Date.now();
      }
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
          this.fire(message.type, message.data);
        }
      } catch (e) {}
    },
    connect: function() {
      try {
        var that = this;
        var conn = that.conn = new WebSocket(that.uri);
        conn.onopen = function() {
          that._msgs.forEach(function(msg) {
            conn.send(JSON.stringify(msg));
          });
          that._msgs = [];
          that.lastActiveTime = Date.now();
          conn.onmessage = that.receive.bind(that);
        }
      } catch (e) {}
    },
    on: function(event, callback) {
      this._eventHandlers[event] = this._eventHandlers[event] || [];
      this._eventHandlers[event].push(callback);
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
    },
    fire: function(event, data) {
      var that = this;
      if (that._eventHandlers['*']) {
        that._eventHandlers['*'].forEach(function(callback) {
          callback.call(that, data);
        });
      }
      if (that._eventHandlers[event]) {
        that._eventHandlers[event].forEach(function(callback) {
          callback.call(that, data);
        });
      }
    }
  }
  global.WS = WS;
})(this);