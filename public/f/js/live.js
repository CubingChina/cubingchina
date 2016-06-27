(function(global) {
  if (!('WS' in global)) {
    alert('Your browser doesn\'t support, please upgrade!');
    return;
  }
  Vue.config.debug = true;

  //websocket
  var ws = window._ws = new WS('ws://' + location.host + '/ws');
  ws.on('connect', function() {
    ws.send({
      type: 'competition',
      competitionId: state.competitionId
    });
    if (state.results.length == 0) {
      fetchResults();
    }
    ws.send({
      type: 'result',
      action: 'rounds',
    });
    if (options.showMessage) {
      ws.send({
        type: 'chat',
        action: 'fetch'
      });
    }
  }).on('receive', function(data) {
    if (data.onlineNumber) {
      store.dispatch('UPDATE_ONLINE_NUMBER', data.onlineNumber);
    }
  }).on('result.new', function(result) {
    store.dispatch('NEW_RESULT', result);
    newMessageOnResult(result, 'new');
  }).on('result.update', function(result) {
    store.dispatch('UPDATE_RESULT', result);
    newMessageOnResult(result, 'update');
  }).on('result.user', function(results) {
    store.dispatch('UPDATE_USER_RESULTS', results);
  }).on('result.all', function(results) {
    store.dispatch('UPDATE_RESULTS', results);
  }).on('round.all', function(rounds) {
    store.dispatch('UPDATE_ROUNDS', rounds);
  }).on('round.update', function(round) {
    store.dispatch('UPDATE_ROUND', round);
  }).on('message.recent', function(messages) {
    if (options.showMessage) {
      store.dispatch('RECENT_MESSAGES', messages);
    }
  }).on('message.new', function(message) {
    if (options.showMessage) {
      newMessage(message);
    }
  });

  Vue.use(VueRouter);
  Vue.use(Vuex);
  Vue.filter('decodeResult', decodeResult);
  var liveContainer = $('#live-container');
  var isTimeTraveling;
  var state = {
    onlineNumber: 0,
    user: {},
    competitionId: 0,
    events: [],
    filters: [],
    params: {
      event: '',
      round: '',
      filter: 'all'
    },
    loading: false,
    loadingUserResults: false,
    results: [],
    userResults: [],
    messages: []
  };
  var events = {};
  var eventRounds = {};
  var current = {};
  var mutations = {
    CHANGE_PARAMS: function(state, params) {
      state.params = params;
    },
    UPDATE_ROUNDS: function(state, rounds) {
      rounds.forEach(function(round) {
        $.extend(eventRounds[round.event][round.id], round);
      });
    },
    UPDATE_ROUND: function(state, round) {
      $.extend(eventRounds[round.event][round.id], round);
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
    UPDATE_USER_RESULTS: function(state, userResults) {
      state.userResults = userResults;
      state.loadingUserResults = false;
    },
    LOADING_RESULTS: function(state) {
      state.loading = true;
      state.results = [];
    },
    LOADING_USER_RESULTS: function(state) {
      state.loadingUserResults = true;
      state.userResults = [];
    },
    RECENT_MESSAGES: function(state, messages) {
      var ids = {};
      state.messages.forEach(function(message) {
        ids[message.id] = message.id;
      });
      messages.forEach(function(message) {
        if (!ids[message.id]) {
          state.messages.push(message);
        }
      });
      Vue.nextTick(function() {
        newMessage({}, true);
      });
    },
    NEW_MESSAGE: function(state, message) {
      if (!message.id && !message.isSelf) {
        return;
      }
      state.messages.push(message);
      if (state.messages.length > 1000) {
        state.messages.splice(0, 1);
      }
    },
    UPDATE_ONLINE_NUMBER: function(state, onlineNumber) {
      state.onlineNumber = onlineNumber;
    }
  };
  $.extend(state, liveContainer.data());
  var options = {
    enableEntry: true,
    showMessage: true,
    alertResult: true,
    alertRecord: true
  };
  $.extend(options, window.store.get('live_options'));
  state.events.forEach(function(event) {
    events[event.id] = event;
    eventRounds[event.id] = {};
    event.rounds.forEach(function(round) {
      eventRounds[event.id][round.id] = round;
      if (!current.event && round.status == 2) {
        current.event = event.id;
        current.round = round.id;
      }
    });
  });
  if (!current.event) {
    current = {
      event: state.params.event,
      round: state.params.round
    }
  }
  var mixin = {
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
          var params = state.params;
          if (eventRounds[params.event] && eventRounds[params.event][params.round]) {
            return eventRounds[params.event][params.round].name;
          }
        },
        events: function(state) {
          return state.events;
        },
        event: function(state) {
          return state.params.event;
        },
        round: function(state) {
          return state.params.round;
        },
        isCurrentRoundOpen: function(state) {
          var round = eventRounds[state.params.event][state.params.round];
          return round.status != 1;
        },
        results: function(state) {
          return state.results;
        },
        loading: function(state) {
          return state.loading;
        },
        userResults: function(state) {
          return state.userResults;
        },
        loadingUserResults: function(state) {
          return state.loadingUserResults;
        },
        filters: function(state) {
          return state.filters;
        },
        onlineNumber: function(state) {
          return state.onlineNumber;
        }
      }
    },
  };
  //vuex
  var store = new Vuex.Store({
    state: state,
    strict: true,
    mutations: mutations
  });
  Vue.mixin(mixin);
  //main component
  var vm = Vue.extend({
    template: '#live-container-template',
    store: store,
    data: function() {
      return {
        options: options,
        currentUser: {}
      };
    },
    watch: {
      'options.enableEntry': function() {
        window.store.set('live_options', this.options);
      },
      'options.showMessage': function() {
        window.store.set('live_options', this.options);
      },
      'options.alertResult': function() {
        window.store.set('live_options', this.options);
      },
      'options.alertRecord': function() {
        window.store.set('live_options', this.options);
      }
    },
    methods: {
      getRecordClass: function(record) {
        if (record == 'NR' || record == 'WR') {
          return 'record-' + record.toLowerCase();
        } else {
          return 'record-cr';
        }
      },
      getEventName: function(event) {
        return events[event] && events[event].name;
      },
      getRoundName: function(event, round) {
        return eventRounds[event][round] && eventRounds[event][round].name;
      },
      showOptions: function() {
        $('#options-modal').modal();
      }
    },
    components: {
      chat: {
        props: ['options'],
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
              action: 'send',
              content: that.message,
              params: state.params
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
        props: ['options'],
        data: function() {
          return {
            eventRound: null,
            filter: 'all',
            current: {},
            cut_off: 0,
            time_limit: 0,
            number: 0,
            page: 1,
            limit: 300
          }
        },
        computed: {
          offset: function() {
            return (this.page - 1) * this.limit;
          },
          totalPage: function() {
            return Math.ceil(this.results.length / this.limit);
          }
        },
        watch: {
          '$store.state.params': function(params) {
            var that = this;
            that.eventRound = {
              event: params.event,
              round: params.round
            }
            that.filter = params.filter;
            that.page = 1;
            var round = eventRounds[params.event][params.round];
            that.cut_off = round.cut_off;
            that.time_limit = round.time_limit;
            that.number = round.number;
          }
        },
        ready: function() {
          this.eventRound = {
            event: current.event,
            round: current.round
          };
        },
        template: '#result-template',
        methods: {
          hasAverage: function(result) {
            var r = result || eventRounds[this.event][this.round];
            return r.format == 'a' || r.format == 'm' || (this.event == '333bf' && r.format == '3');
          },
          getRecordClass: function(record) {
            if (record == 'NR' || record == 'WR') {
              return 'record-' + record.toLowerCase();
            } else {
              return 'record-cr';
            }
          },
          showRoundSettings: function() {
            $('#round-settings-modal').modal();
          },
          saveRoundSettings: function() {
            var that = this;
            var attrs = ['cut_off', 'time_limit', 'number'];
            var i;
            for (i = 0; i < attrs.length; i++) {
              $('#' + attrs[i]).parent().removeClass('has-error');
              if (isNaN(that[attrs[i]])) {
                $('#' + attrs[i]).parent().addClass('has-error');
                return;
              }
            }
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.event,
                id: state.params.round,
                cut_off: that.cut_off,
                time_limit: that.time_limit,
                number: that.number
              }
            });
            $('#round-settings-modal').modal('hide');
          },
          closeRound: function() {
            this.current = {};
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.event,
                id: state.params.round,
                status: 1
              }
            });
            $('#round-settings-modal').modal('hide');
          },
          openRound: function() {
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.event,
                id: state.params.round,
                status: 0
              }
            });
            $('#round-settings-modal').modal('hide');
          },
          resetCompetitors: function() {
            if (this.results.length == 0 || confirm('Do you want reset competitors?')) {
              ws.send({
                type: 'result',
                action: 'reset',
                round: {
                  event: state.params.event,
                  id: state.params.round,
                }
              });
            }
          },
          goToUser: function(user) {
            this.$parent.currentUser = user;
            $('#user-results-modal').modal('show');
            store.dispatch('LOADING_USER_RESULTS');
            ws.send({
              type: 'result',
              action: 'user',
              user: user
            });
          },
          edit: function(result) {
            if (this.hasPermission && options.enableEntry && this.isCurrentRoundOpen) {
              this.current = $.extend({}, result);
              this.$nextTick(function() {
                $('.input-panel-result input').eq(0).focus();
              });
            }
          },
          changeParams: function() {
            store.dispatch('CHANGE_PARAMS', {
              event: this.eventRound.event,
              round: this.eventRound.round,
              filter: this.filter
            });
          },
          isAdvanced: function(result) {
            if (this.filter != 'all') {
              return false;
            }
            if (result.round == 'c' || result.round == 'f') {
              return result.best > 0 && result.pos <= 3;
            }
            var i;
            for (i = 0; i < events[result.event].rounds.length; i++) {
              if (events[result.event].rounds[i].id == result.round) {
                if (events[result.event].rounds[i + 1]) {
                  return result.best > 0 && result.pos <= events[result.event].rounds[i + 1].number;
                }
              }
            }
            return false;
          }
        },
        components: {
          'input-panel': {
            props: ['result'],
            data: function() {
              return {
                lastIndex: null,
                currentIndex: null,
                competitor: {
                  name: ''
                },
                value1: 0,
                value2: 0,
                value3: 0,
                value4: 0,
                value5: 0,
                best: 0,
                worst: 0,
                average: 0,
                searchText: '',
                searching: false,
                selectedIndex: 0
              }
            },
            computed: {
              competitors: function() {
                var that = this;
                var searchText = that.searchText.toLowerCase();
                return that.results.filter(that.filterCompetitors.bind(that)).sort(function(resA, resB) {
                  if (!/^\d+$/.test(searchText)) {
                    var temp = resA.user.name.toLowerCase().indexOf(searchText) - resB.user.name.toLowerCase().indexOf(searchText);
                    if (temp == 0) {
                      temp = resA.user.name < resB.user.name ? -1 : 1;
                    }
                    return temp;
                  }
                  return resA.number - resB.number
                }).slice(0, 5)
              }
            },
            watch: {
              round: function() {
                this.searchText = '';
              },
              event: function() {
                this.searchText = '';
              },
              result: function(result) {
                var that = this;
                that.competitor = result.user;
                that.value1 = result.value1 || 0;
                that.value2 = result.value2 || 0;
                that.value3 = result.value3 || 0;
                that.value4 = result.value4 || 0;
                that.value5 = result.value5 || 0;
                that.searchText = result.number || '';
              },
              searchText: function(searchText) {
                this.selectedIndex = 0;
                if (searchText == '') {
                  this.result = {};
                }
              }
            },
            attached: function() {
              var that = this;
              $(window).on('resize', function() {
                that.$el.style.width = that.$el.parentNode.clientWidth - 30 + 'px';
              }).trigger('resize');
            },
            methods: {
              isDisabled: function(index) {
                var that = this;
                var result = that.result;
                if (!result || !result.id || !this.$parent.isCurrentRoundOpen) {
                  return true;
                }
                var round = eventRounds[state.params.event][state.params.round];
                if (round.cut_off > 0) {
                  var num = round.format == 'a' ? 2 : 1;
                  var passed = false;
                  for (var i = 1; i <= num; i++) {
                    if (that.result['value' + i] > 0 && that.result['value' + i] / 100 < round.cut_off) {
                      passed = true;
                      break;
                    }
                  }
                  return !(passed || index < num);
                }
                return false;
              },
              keydown: function(e) {
                var code = e.which;
                var that = this;
                switch (code) {
                  case 107:
                  case 40:
                  case 38:
                  case 9:
                    if (e.shiftKey || code == 107 || code == 38) {
                      $('.result-input:not([disabled])').last().focus();
                    } else {
                      $('.result-input:not([disabled])').first().focus();
                    }
                    break;
                  case 13:
                    that.save();
                    break;
                }
              },
              save: function() {
                var that = this;
                calculateAverage(that.result);
                store.dispatch('UPDATE_RESULT', that.result);
                ws.send({
                  type: 'result',
                  action: 'update',
                  result: that.result
                });
                that.result = {};
                $('#input-panel-name').focus();
              },
              filterCompetitors: function(result) {
                var that = this;
                var searchText = that.searchText.trim();
                if (searchText == '') {
                  return false;
                }
                if (/^\d+$/.test(searchText)) {
                  return !!result.number.toString().match(searchText);
                }
                return !!result.user.name.match(new RegExp(searchText, 'i'));
              },
              enter: function() {
                if (this.competitors[this.selectedIndex]) {
                  this.selectCompetitor(this.competitors[this.selectedIndex]);
                }
              },
              up: function() {
                var length = this.competitors.length;
                if (length) {
                  this.selectedIndex = (this.selectedIndex + length - 1) % length;
                }
              },
              down: function() {
                var length = this.competitors.length;
                if (length) {
                  this.selectedIndex = (this.selectedIndex + 1) % length;
                }
              },
              selectCompetitor: function(result) {
                this.name = result.number;
                this.$parent.edit(result);
              }
            },
            vuex: {
              getters: {
                inputNum: function(state) {
                  var params = state.params;
                  var round = eventRounds[params.event] && eventRounds[params.event][params.round];
                  var format = round && round.format;
                  switch (format) {
                    case '1':
                      return 1;
                    case '2':
                      return 2;
                    case '3':
                    case 'm':
                      return 3;
                    default:
                      return 5;
                  }
                },
                minInputNum: function(state) {
                  var params = state.params;
                  var round = eventRounds[params.event] && eventRounds[params.event][params.round];
                  var format = round && round.format;
                  switch (format) {
                    case '1':
                      return 1;
                    case '2':
                      return 2;
                    case '3':
                      return 3;
                    case 'm':
                      return 1;
                    default:
                      return 2;
                  }
                }
              }
            },
            template: '#input-panel-template',
            components: {
              'result-input': {
                props: ['value', 'index'],
                data: function() {
                  return {
                    subIndex: 2,
                    tried: '',
                    solved: '',
                    time: ''
                  }
                },
                watch: {
                  time: function() {
                    this.calculateValue();
                  },
                  tried: function() {
                    this.calculateValue();
                  },
                  solved: function() {
                    this.calculateValue();
                  },
                  '$parent.result.id': function() {
                    var that = this;
                    var time = decodeResult(that.value, that.event);
                    if (that.event === '333mbf') {
                      that.subIndex = 0;
                      if (time.indexOf('/') > -1) {
                        var match = time.match(/^(\d+)\/(\d+) (.+)$/);
                        that.solved = match[1];
                        that.tried = match[2];
                        that.time = match[3].replace(/[:\.]/g, '');
                      } else {
                        that.solved = that.tried = '';
                        that.time = time;
                      }
                    } else {
                      that.time = time.replace(/[:\.]/g, '');
                      that.subIndex = 2;
                    }
                  }
                },
                methods: {
                  calculateValue: function() {
                    var that = this;
                    var round = eventRounds[state.params.event][state.params.round];
                    that.value = encodeResult(that.formatTime(that.time), that.event, false, that.tried, that.solved);
                    if (round.time_limit > 0 && that.value / 100 > round.time_limit) {
                      that.time = 'DNF';
                    }
                    if (that.event === '333fm' && that.value > 80) {
                      that.time = 'DNF';
                    }
                  },
                  formatTime: function(time) {
                    if (time == 'DNF' || time == 'DNS' || time == '' || this.event == '333fm') {
                      return time;
                    }
                    var minute = time.length > 4 ? parseInt(time.slice(0, -4)) : 0;
                    var second = time.length > 2 ? parseInt(time.slice(0, -2).slice(-2)) : 0;
                    var msecond = parseInt(time.slice(-2));
                    if (this.event === '333mbf') {
                      if (this.solved == '' || this.tried == '') {
                        return '';
                      }
                      minute = second;
                      second = msecond;
                      msecond = 0;
                    }
                    return [
                      minute ? minute + ':' : '',
                      second + '.',
                      msecond
                    ].join('');
                  },
                  focus: function(subIndex) {
                    this.subIndex = subIndex;
                    this.$parent.currentIndex = this.index;
                  },
                  blur: function(e) {
                    this.$parent.currentIndex = null;
                    this.$parent.lastIndex = null;
                  },
                  keydown: function(e, attr) {
                    var code = e.which;
                    var that = this;
                    var value = that[attr];
                    switch (code) {
                      //D,/ pressed
                      case 68:
                      case 111:
                        that.time = 'DNF';
                        break;
                      //S,* pressed
                      case 106:
                      case 83:
                        that.time = 'DNS';
                        break;
                      case 8:
                      case 109:
                        if (that.$parent.lastIndex != that.$parent.currentIndex || that.time == 'DNF' || that.time == 'DNS') {
                          if (that.time === 'DNF' || that.time === 'DNS') {
                            that.solved = that.tried = that.time = '';
                          }
                          value = '';
                        }
                        that.$parent.lastIndex = that.$parent.currentIndex;
                        that[attr] = value.slice(0, -1);
                        break;
                      case 107:
                      case 38:
                      case 9:
                        if (e.shiftKey || code == 107 || code == 38) {
                          var input = $(e.target);
                          var inputs = $('.result-input:not([disabled])');
                          var index = inputs.index(input);
                          if (index > 0) {
                            inputs.eq(index - 1).focus();
                          }
                          break;
                        }
                      case 40:
                      case 13:
                        var input = $(e.target);
                        var inputs = $('.result-input:not([disabled])');
                        var index = inputs.index(input);
                        if (index < inputs.length - 1) {
                          inputs.eq(index + 1).focus();
                        } else {
                          $('#save').focus();
                        }
                        break;
                      //small keyboard
                      case 96:
                      case 97:
                      case 98:
                      case 99:
                      case 100:
                      case 101:
                      case 102:
                      case 103:
                      case 104:
                      case 105:
                        code -= 48;
                      //num
                      case 48:
                      case 49:
                      case 50:
                      case 51:
                      case 52:
                      case 53:
                      case 54:
                      case 55:
                      case 56:
                      case 57:
                        if (value.length >= 6) {
                          break;
                        }
                        if (that.$parent.lastIndex != that.$parent.currentIndex || that.time == 'DNF' || that.time == 'DNS') {
                          if (that.time === 'DNF' || that.time === 'DNS') {
                            that.solved = that.tried = that.time = '';
                          }
                          value = '';
                        }
                        if ((that.event === '333fm' || that.subIndex < 2) && value.length >= 2) {
                          break;
                        }
                        if (that.event === '333mbf' && value.length >= 4) {
                          break;
                        }
                        value += code - 48;
                        that[attr] = value;
                        if (that.$parent.lastIndex != that.$parent.currentIndex) {
                          that.$parent.lastIndex = that.$parent.currentIndex;
                        }
                        break;
                      default:
                        e.preventDefault();
                        break;
                    }
                  }
                },
                template: '#result-input-template'
              }
            }
          }
        }
      }
    }
  });

  //router
  var router = new VueRouter();
  router.map({
    '/event/:event/:round/:filter': {
      component: {}
    }
  });
  store.watch(function(state) {
    return state.params;
  }, function(params) {
    router.go(['/event', params.event, params.round, params.filter].join('/'));
    fetchResults();
  }, {
    deep: true,
    sync: true,
    // immediate: true
  });
  router.afterEach(function(transition) {
    var params = transition.to.params;
    if (params.event == state.params.event && params.round == state.params.round && params.filter == state.params.filter) {
      return;
    }
    store.dispatch('CHANGE_PARAMS', params);
  });
  router.redirect({
    '*': ['/event', current.event, current.round, state.params.filter].join('/')
  });
  router.start(vm, liveContainer.get(0));

  var newMessage = function() {
    var container = $('.message-container');
    var ul = container.find('ul');
    return function(message, scroll) {
      store.dispatch('NEW_MESSAGE', message);
      if (scroll || container.height() + container.scrollTop() > ul.height() - 30) {
        Vue.nextTick(function() {
          container.scrollTop(ul.height());
        });
      }
    };
  }();
  function newMessageOnResult(result, type) {
    if (result.best == 0) {
      return;
    }
    if (options.alertResult) {
      var message = {
        id: +new Date(),
        user: {
          name: 'System'
        },
        time: Math.floor(+new Date() / 1000)
      };
      var content = [];
      var temp = [];
      temp.push(result.user.name);
      temp.push(events[result.event] && events[result.event].name);
      temp.push(eventRounds[result.event] && eventRounds[result.event][result.round] && eventRounds[result.event][result.round].name);
      content.push(temp.join(' - '));
      if (result.average != 0) {
        content.push('Average: ' + decodeResult(result.average, result.event));
      }
      content.push('Single: ' + decodeResult(result.best, result.event));
      temp = [];
      var num = result.format == 'a' ? 5 : (result.format == 'm' ? 3 : parseInt(result.format));
      for (var i = 1; i <= num; i++) {
        if (result['value' + i] != 0) {
          temp.push(decodeResult(result['value' + i], result.event));
        } else {
          temp.push('--');
        }
      }
      content.push('Detail: ' + temp.join('    '));
      message.content = '<p class="text-danger">' + content.join('<br>') + '</p>';
      newMessage(message);
    }
    //check record
    if (options.alertRecord && (result.regional_single_record != '' || result.regional_average_record != '')) {
      //@todo alert record
    }
  }
  function fetchResults() {
    if (state.loading) {
      return;
    }
    store.dispatch('LOADING_RESULTS');
    ws.send({
      type: 'result',
      action: 'fetch',
      params: state.params
    });
  }
  function calculateAverage(result) {
    var best = 999999999;
    var worst = 0;
    var hasAverage = true;
    var i, value, DNFCount = 0, zeroCount = 0, sum = 0;
    var num = result.format == 'a' ? 5 : (result.format == 'm' ? 3 : parseInt(result.format));
    for (i = 1; i <= num; i++) {
      value = result['value' + i];
      sum += value;
      if (value > 0 && value < best) {
        best = value;
      }
      if (value == 0) {
        zeroCount++;
      }
      if (value < 0) {
        DNFCount++;
        worst = value;
      } else if (value > worst && worst >= 0) {
        worst = value;
      }
    }
    result.best = best;
    //check best
    if (result.best === 999999999) {
      result.best = worst == 0 ? 0 : -1;
    }
    //
    if ((result.format == 'a' || result.format == 'm') && zeroCount > 0) {
      hasAverage = false;
    }
    if (DNFCount > 1 || (DNFCount == 1 && (result.format == 'm' || result.format == '3'))) {
      hasAverage = false;
    }
    if (result.format == '1' || result.format == '2' || (result.format == '3' && result.event != '333bf')) {
      hasAverage = false;
    }
    if (hasAverage) {
      if (result.format == 'm' || result.format == '3') {
        if (result.event === '333fm') {
          sum *= 100;
        }
        result.average = Math.round(sum / 3);
      } else {
        result.average = Math.round((sum - best - worst) / 3);
      }
    } else if (result.format == 'm' || result.format == 'a') {
      result.average = zeroCount > 0 ? 0 : -1;
    } else if (result.event == '333bf') {
      result.average = 0;
    }
  }
  function encodeResult(result, event, isAverage, tried, solved) {
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
      var missed = tried - solved;
      var difference = solved - missed;
      if (missed > solved || solved < 2) {
        return -1;
      }
      var match = result.match(/(?:(\d+)?:)?(\d{1,2})/);
      if (!match) {
        return 0;
      }
      var minute = match[1] ? parseInt(match[1]) : 0;
      var second = parseInt(match[2]);
      second = Math.min(minute * 60 + second, Math.min(6, tried) * 600);
      return (99 - difference) * 1e7 + second * 100 + missed;
    } else {
      var match = result.match(/(?:(\d+)?:)?(\d{1,2})\.(\d{1,2})/);
      if (!match) {
        return 0;
      }
      var minute = match[1] ? parseInt(match[1]) : 0;
      var second = parseInt(match[2]);
      var msecond = parseInt(match[3]);
      return (minute * 60 + second) * 100 + msecond;
    }
  }
  function decodeResult(result, event) {
    var time;
    if (result == 0 || result == undefined) {
      return '';
    }
    result = parseInt(result);
    if (result == -1) {
      return 'DNF';
    }
    if (result == -2) {
      return 'DNS';
    }
    if (event === '333fm') {
      if (result > 1000) {
        time = (result / 100).toFixed(2);
      } else {
        time = result + '';
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
    if (hour == 1) {
      minute += 60;
      hour = 0;
    }
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
    var temp = 0;
    if (resA.format == 'm' || resA.format == 'a') {
      if (resA.average > 0 && resB.average <= 0) {
        return -1
      }
      if (resB.average > 0 && resA.average <= 0) {
        return 1
      }
      temp = resA.average - resB.average;
    }
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