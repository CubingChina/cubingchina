(function(global) {
  if (!('WS' in global)) {
    alert('Your browser doesn\'t support, please upgrade!');
    return;
  }

  //websocket
  var ws = new WS('ws://' + location.host + '/ws');
  ws.on('connect', function() {
    ws.send({
      type: 'competition',
      competitionId: state.competitionId
    });
    fetchResults();
  }).on('result.new', function(result) {
    store.dispatch('NEW_RESULT', result);
  }).on('message.new', function(message) {
    newMessage(message);
  }).on('result.all', function(results) {
    store.dispatch('UPDATE_RESULTS', results);
  });

  Vue.use(VueRouter);
  Vue.use(Vuex);
  var liveContainer = $('#live-container');
  var isTimeTraveling;
  var state = {
    user: {},
    competitionId: 0,
    events: [],
    params: {
      event: '',
      round: '',
    },
    loading: false,
    results: [],
    messages: []
  };
  var events = {};
  var mutations = {
    CHANGE_EVENT_ROUND: function(state, params) {
      state.params = params;
    },
    NEW_RESULT: function(state, result) {
      if (result.competitionId == state.competitionId && result.event == state.params.event && result.round == state.params.round) {
        result.pos = '';
        result.isNew = true;
        var results = state.results;
        var index = findIndex(results, result);
        results.splice(index, 0, result);
        calcPos(results, result);
      }
    },
    UPDATE_RESULT: function(state, result) {
      if (result.competitionId == state.competitionId && result.event == state.params.event && result.round == state.params.round) {
        var results = state.results;
        var i = 0, len = results.length;
        result.pos = '';
        result.isNew = true;
        for (; i < len; i++) {
          if (results[i].id == result.id) {
            result.user = results[i].user;
            results[i] = result;
            break;
          }
        }
        results.sort(compare);
        calcPos(results, result);
      }
    },
    UPDATE_RESULTS: function(state, results) {
      results.sort(compare);
      calcPos(results, {});
      state.results = results;
      state.loading = false;
    },
    NEW_MESSAGE: function(state, message) {
      state.messages.push(message);
      if (state.messages.length > 1000) {
        state.messages.splice(0, 1);
      }
    }
  };
  $.extend(state, liveContainer.data());
  state.events.forEach(function(event) {
    events[event.id] = event;
  });

  //vuex
  var store = new Vuex.Store({
    state: state,
    mutations: mutations
  });
  //main component
  var vm = Vue.extend({
    template: '#live-container-template',
    store: store,
    components: {
      chat: {
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
        template: '#chat-template',
        methods: {
          send: function(e) {
            var that = this;
            if (that.message.trim() == '') {
              return;
            }
            ws.send({
              type: 'chat',
              content: that.message
            });
            newMessage({
              user: state.user,
              content: that.message,
              // time: Date.now() / 1000,
              isSelf: true
            }, true);
            that.message = '';
          }
        },
        components: {
          message: {
            props: ['message'],
            template: '#message-template',
            filters: {
              formatTime: function(time) {
                return time ? moment(new Date(time * 1000)).format('HH:mm:ss') : '';
              }
            }
          }
        }
      },
      result: {
        data: function() {
          return {
            eventRound: null
          }
        },
        watch: {
          '$store.state.params': function(params) {
            this.eventRound = {
              event: params.event,
              round: params.round
            }
          }
        },
        vuex: {
          getters: {
            hasPermission: function(state) {
              var user = state.user;
              return user.isOrganizer || user.isDelegate || user.isAdmin;
            },
            eventName: function(state) {
              return events[state.params.event] && events[state.params.event].name;
            },
            roundName: function(state) {
              if (events[state.params.event]) {
                var rounds = events[state.params.event].rounds;
                for (var i = 0; i < rounds.length; i++) {
                  if (rounds[i].id == state.params.round) {
                    return rounds[i].name;
                  }
                }
              }
            },
            loading: function(state) {
              return state.loading;
            },
            event: function(state) {
              return state.params.event;
            },
            round: function(state) {
              return state.params.round;
            },
            events: function(state) {
              return state.events;
            },
            results: function(state) {
              return state.results;
            }
          }
        },
        template: '#result-template',
        methods: {
          click: function(result) {
            console.log(result)
          },
          changeEventRound: function() {
            store.dispatch('CHANGE_EVENT_ROUND', {
              event: this.eventRound.event,
              round: this.eventRound.round
            });
          }
        },
        filters: {
          decodeResult: function(result, event) {
            return decodeResult(result, event);
          }
        },
        components: {
          'input-panel': {
            data: function() {
              return {
                value1: 0,
                value2: 0,
                value3: 0,
                value4: 0,
                value5: 0,
                best: 0,
                worst: 0,
                average: 0,
                result: {
                  id: null,
                  event: '',
                  name: '',
                  number: 0
                }
              }
            },
            filters: {
              result: {
                get: function(value) {
                  return decodeResult(value, this.result.event);
                },
                set: function(value) {
                  return encodeResult(value, this.result.event);
                }
              }
            },
            template: '#input-panel-template'
          }
        }
      }
    }
  });

  //router
  var router = new VueRouter();
  router.map({
    '/:event/:round': {
      component: {}
    }
  });
  store.watch(function(state) {
    return state.params;
  }, function(params) {
    router.go(['', params.event, params.round].join('/'));
    fetchResults();
  }, {
    deep: true,
    sync: true,
    // immediate: true
  });
  router.afterEach(function(transition) {
    var params = transition.to.params;
    if (params.event == state.params.event && params.round == state.params.round) {
      return;
    }
    store.dispatch('CHANGE_EVENT_ROUND', params);
  });
  router.redirect({
    '*': ['', state.params.event, state.params.round].join('/')
  });
  router.start(vm, liveContainer.get(0));

  var newMessage = function() {
    var container = $('.message-container');
    var ul = container.find('ul');
    return function(message, scroll) {
      store.dispatch('NEW_MESSAGE', message);
      if (scroll || container.height() + container.scrollTop() > ul.height()) {
        Vue.nextTick(function() {
          container.scrollTop(ul.height());
        });
      }
    };
  }();
  function fetchResults() {
    if (state.loading) {
      return;
    }
    state.loading = true;
    state.results = [];
    ws.send({
      type: 'result',
      action: 'fetch',
      params: state.params
    });
  }
  function encodeResult(result, event, isAverage) {
    if (result === 'DNF') {
      return -1;
    }
    if (result === 'DNS') {
      return -2;
    }
    if (result === '') {
      return 0;
    }
    if (event === '333fm') {
      if (isAverage) {
        return parseFloat(result) * 100;
      }
      return parseInt(result);
    } else if (event === '333mbf') {

    }
  }
  function decodeResult(result, event) {
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
      if (msecond < 10) {
        msecond = '0' + msecond;
      }
      time = formatSecond(second) + '.' + msecond;
    }
    return time;
  }
  function formatSecond(second, multi) {
    if (multi && second == 99999) {
      return 'unknown';
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
    for (var i = 1; i < temp.length; i++) {
      if (temp[i - 1] < 10) {
        temp[i - 1] = '0' + temp[i - 1];
      }
    }
    return temp.reverse().join(':');
  }
  function findIndex(results, result) {
    var middle, temp;
    var left = 0, right = results.length - 1;
    while (left <= right) {
      middle = (left + right) >> 1;
      temp = compare(result, results[middle], true);
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
      if (!results[i - 1] || compare(results[i - 1], results[i], true) < 0) {
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
  function compare(resA, resB, onlyResult) {
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
    if (!onlyResult && temp == 0) {
      temp = resA.user.name < resB.user.name ? -1 : 1;
    }
    return temp;
  }
})(this);