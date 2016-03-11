(function(global) {
  if (!('WS' in global)) {
    alert('Your browser doesn\'t support, please upgrade!');
    return;
  }
  Vue.use(VueRouter);
  Vue.use(Vuex);
  var liveContainer = $('#live-container');
  var state = {
    competitionId: 0,
    events: {},
    event: '',
    round: '',
    results: [],
    messages: []
  };
  var mutations = {
    CHANGE_EVENT: function(state, event) {
      if (state.events[event] !== undefined) {
        state.event = event;
        state.results = [];
        ws.send({
          type: 'result',
          command: 'event',
          event: event
        });
      }
    },
    CHANGE_ROUND: function(state, round) {
      if (state.events[state.event].indexOf(round) > -1) {
        state.round = round;
        state.results = [];
        ws.send({
          type: 'result',
          command: 'round',
          round: round
        });
      }
    },
    NEW_RESULT: function(state, result) {
      if (result.competitionId == state.competitionId && result.event == state.event && result.round == state.round) {
        result.pos = '';
        result.isNew = true;
        var results = state.results;
        var index = findIndex(results, result);
        results.splice(index, 0, result);
        calcPos(results, result);
      }
    },
    UPDATE_RESULT: function(state, result) {
      if (result.competitionId == state.competitionId && result.event == state.event && result.round == state.round) {
        var results = state.results;
        var i = 0, len = results.length;
        result.pos = '';
        result.isNew = true;
        for (; i < len; i++) {
          if (results[i].id == result.id) {
            results[i] = result;
            break;
          }
        }
        results.sort(compare);
        calcPos(results, result);
      }
    },
    NEW_MESSAGE: function(state, message) {
      state.messages.push(message);
      if (state.messages.length > 1000) {
        state.messages.splice(0, 1);
      }
    }
  };
  $.extend(state, liveContainer.data());
  var store = new Vuex.Store({
    state: state,
    mutations: mutations
  });
  var vm = new Vue({
    el: liveContainer.get(0),
    template: $('#live-container-template').html(),
    store: store,
    components: {
      chat: Vue.extend({
        data: function() {
          return {
            message: ''
          }
        },
        vuex: {
          getters: {
            messages: function(state) {
              return state.messages;
            }
          }
        },
        template: $('#chat-template').html(),
        methods: {
          send: function(e) {
            var that = this;
            if (that.message.trim() == '') {
              return;
            }
            ws.send({
              type: 'chat',
              text: that.message
            });
            newMessage({
              text: that.message
            }, true);
            that.message = '';
          }
        }
      }),
      result: Vue.extend({
        vuex: {
          getters: {
            results: function(state) {
              return state.results;
            }
          }
        },
        template: $('#result-template').html(),
        filters: {
          formatTime: function(result, event) {
            var time;
            result = parseInt(result);
            if (result == -1) {
              return 'DNF';
            }
            if (result == -2) {
              return 'DNS';
            }
            if (result == 0) {
              return '';
            }
            if (event === '333fm') {
              if (result > 1000) {
                time = (result / 100).toFixed(2);
              } else {
                time = result;
              }
            } else if (event === '333mbf') {
              var difference = 99 - Math.floor(result / 1e7);
              var missed = result % 100;
              time = (difference + missed) + '/' + (difference + missed * 2) + ' ' + formatSecond(Math.floor(result / 100) % 1e5, true);
            } else { 
              var msecond = result % 100;
              var second = Math.floor(result / 100);
              time = formatSecond(second) + '.' + msecond;
            }
            return time;
          }
        }
      })
    }
  });
  var ws = new WS('ws://' + location.host + '/ws');
  ws.on('connect', function() {
    ws.send({
      type: 'competition',
      competitionId: store.competitionId
    });
  }).on('newresult', function(result) {
    store.dispatch('NEW_RESULT', result);
  }).on('newmessage', function(message) {
    newMessage(message);
  });

  var newMessage = function() {
    var container = $('.message-container');
    var ul = container.find('ul');
    return function(message, scroll) {
      store.dispatch('NEW_MESSAGE', message);
      if (scroll || container.height() + container.scrollTop() > ul.height()) {
        vm.$nextTick(function() {
          container.scrollTop(ul.height());
        });
      }
    };
  }();
  function formatSecond(second, multi) {
    if (multi) {
      if (second == 99999) {
        return 'unknown';
      }
      if (second == 3600) {
        return '60:00';
      }
    }
    second = parseInt(second);
    var minute = Math.floor(second / 60);
    var hour = Math.floor(minute / 60);
    second = second % 60;
    minute = minute % 60;
    var temp = [second];
    if (hour > 0) {
      temp.push(minute, hour);
    } else if (minute > 0) {
      temp.push(minute);
    }
    return temp.reverse().join(':');
  }
  function findIndex(results, result) {
    var middle, temp;
    var left = 0, right = results.length - 1;
    while (left <= right) {
      middle = (left + right) >> 1;
      temp = compare(result, results[middle]);
      if (temp < 0) {
        right = middle - 1;
      } else {
        left = middle + 1;
      }
    }
    return left;
  }
  function calcPos(results, result) {
    for (var i = 0, len = results.length; i < len; i++) {
      if (!results[i - 1] || compare(results[i - 1], results[i]) < 0) {
        results[i].pos = i + 1;
      } else {
        results[i].pos = results[i - 1].pos;
      }
      if (results[i].best == 0) {
        results[i].pos = '-';
      }
      results[i].isNew = results[i] === result;
    }
  }
  function compare(resA, resB) {
    if (resA.average > 0 && resB.average <= 0) {
      return -1
    }
    if (resB.average > 0 && resA.average <= 0) {
      return 1
    }
    var temp = resA.average - resB.average;
    if (temp == 0) {
      if (resA.best > 0 && resB.best <= 0) {
        return -1
      }
      if (resB.best > 0 && resA.best <= 0) {
        return 1
      }
      temp = resA.best - resB.best;
    }
    return temp;
  }
})(this);