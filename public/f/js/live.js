(function(global) {
  if (!('WS' in global)) {
    alert('Your browser doesn\'t support, please upgrade!');
    return;
  }
  Vue.config.debug = true;

  //websocket
  var ws = new WS('ws://' + location.host + '/ws');
  ws.threshold = 55000;
  ws.on('connect', function() {
    ws.send({
      type: 'competition',
      competitionId: state.c
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
  }).on('*', function(data, origin) {
    if (origin && origin.onlineNumber) {
      store.dispatch('UPDATE_ONLINE_NUMBER', origin.onlineNumber);
    }
  }).on('users', function(users) {
    allUsers = users;
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
  }).on('message.disable', function(disableChat) {
    options.disableChat = disableChat;
  });

  Vue.use(VueRouter);
  Vue.use(Vuex);
  Vue.filter('decodeResult', decodeResult);
  var liveContainer = $('#live-container');
  var state = {
    onlineNumber: 0,
    user: {},
    c: 0,
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
  var lastFetchRoundsTime = Date.now();
  var events = {};
  var eventRounds = {};
  var allUsers = {};
  var current = {};
  var mutations = {
    CHANGE_PARAMS: function(state, params) {
      state.params = params;
    },
    UPDATE_ROUNDS: function(state, rounds) {
      rounds.forEach(function(round) {
        $.extend(eventRounds[round.e][round.i], round);
      });
      lastFetchRoundsTime = Date.now();
    },
    UPDATE_ROUND: function(state, round) {
      $.extend(eventRounds[round.e][round.i], round);
    },
    NEW_RESULT: function(state, result) {
      if (result.c == state.c && result.e == state.params.e && result.r == state.params.r) {
        result.p = '';
        result.isNew = true;
        var results = state.results;
        var index = findIndex(results, result);
        results.splice(index, 0, result);
        calculatePos(results, result);
      }
    },
    UPDATE_RESULT: function(state, result) {
      if (result.c == state.c && result.e == state.params.e && result.r == state.params.r) {
        var results = state.results;
        var i = 0, len = results.length;
        result.p = '';
        result.isNew = true;
        for (; i < len; i++) {
          if (results[i].i == result.i) {
            // result.user = results[i].user;
            results[i] = result;
            break;
          }
        }
        results.sort(compare);
        calculatePos(results, result);
      }
    },
    UPDATE_RESULTS: function(state, results) {
      results.sort(compare);
      calculatePos(results, {});
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
        message.type = 'normal';
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
    alertRecord: true,
    disableChat: false
  };
  var storedOptions = window.store.get('live_options');
  if (storedOptions && storedOptions.disableChat) {
    delete storedOptions.disableChat;
  }
  $.extend(options, storedOptions);
  state.events.forEach(function(event) {
    events[event.i] = event;
    eventRounds[event.i] = {};
    event.rs.forEach(function(round) {
      eventRounds[event.i][round.i] = round;
      if (!current.e && round.s == 2) {
        current.e = event.i;
        current.r = round.i;
      }
    });
  });
  if (!current.e) {
    current = {
      e: state.params.e,
      r: state.params.r
    }
  }
  var mixin = {
    methods: {
      getRecordClass: function(record) {
        if (record == 'NR' || record == 'WR') {
          return 'record-' + record.toLowerCase();
        } else {
          return 'record-cr';
        }
      },
      getEventName: function(result) {
        var event = getEvent(result);
        return event && event.name;
      },
      getRoundName: function(result) {
        var round = getRound(result);
        return round && round.name;
      },
      getUser: function(number) {
        return getUser(number);
      },
      getResultDetail: function(result) {
        var detail = [];
        var i, value;
        for (i = 0; i < 5; i++) {
          value = decodeResult(result.v[i], result.e);
          value = (value + '            ').slice(0, result.e === '333mbo' || result.e === '333mbf' ? 13 : 8);
          detail.push(value);
        }
        return '<pre>' + detail.join('   ') + '</pre>';
      }
    },
    vuex: {
      getters: {
        hasPermission: function(state) {
          var user = state.user;
          return user.isOrganizer || user.isDelegate || user.isAdmin;
        },
        eventName: function(state) {
          var event = getEvent(state.params)
          return event && event.name;
        },
        roundName: function(state) {
          var round = getRound(state.params);
          return round && round.name;
        },
        events: function(state) {
          return state.events;
        },
        e: function(state) {
          return state.params.e;
        },
        r: function(state) {
          return state.params.r;
        },
        currentRound: function(state) {
          return getRound(state.params);
        },
        isCurrentRoundOpen: function(state) {
          var round = getRound(state.params);
          return round.s != 1;
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
      'options.disableChat': function(disableChat) {
        ws.send({
          type: 'chat',
          action: 'disable',
          disable_chat: disableChat
        });
      },
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
                return time ? moment(new Date(time * 1000)).format('MM-DD HH:mm:ss') : '';
              }
            },
            components: {
              'normal-message': {
                props: ['message'],
                template: '#normal-message-template'
              },
              'result-message': {
                props: ['message', 'result'],
                template: '#result-message-template'
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
            current: {
              v: []
            },
            co: 0,
            tl: 0,
            f: '',
            n: 0,
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
              e: params.e,
              r: params.r
            }
            that.filter = params.filter;
            that.page = 1;
            var round = getRound(params);
            that.co = round.co;
            that.tl = round.tl;
            that.n = round.n;
            that.f = round.f;
          }
        },
        ready: function() {
          var that = this;
          that.eventRound = {
            e: current.e,
            r: current.r
          };
          var round = getRound(that);
          that.co = round.co;
          that.tl = round.tl;
          that.n = round.n;
          that.f = round.f;
        },
        template: '#result-template',
        methods: {
          hasAverage: function(result) {
            var round = getRound(result || this);
            return round.f == 'a' || round.f == 'm' || (round.e == '333bf' && round.f == '3');
          },
          showRoundSettings: function() {
            $('#round-settings-modal').modal();
          },
          saveRoundSettings: function() {
            var that = this;
            var attrs = ['co', 'tl', 'n'];
            var i;
            for (i = 0; i < attrs.length; i++) {
              $('#' + attrs[i]).parent().removeClass('has-error');
              if (isNaN(that[attrs[i]])) {
                $('#' + attrs[i]).parent().addClass('has-error');
                return;
              }
            }
            $('#f').parent().removeClass('has-error');
            if (that.f == '') {
              $('#f').parent().addClass('has-error');
              return;
            }
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.e,
                id: state.params.r,
                cut_off: that.co,
                time_limit: that.tl,
                format: that.f,
                number: that.n
              }
            });
            $('#round-settings-modal').modal('hide');
          },
          closeRound: function() {
            this.current = {
              v: []
            };
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.e,
                id: state.params.r,
                status: 1
              }
            });
            fetchResults();
            $('#round-settings-modal').modal('hide');
          },
          openRound: function() {
            ws.send({
              type: 'result',
              action: 'round',
              round: {
                event: state.params.e,
                id: state.params.r,
                status: 0
              }
            });
            fetchResults();
            $('#round-settings-modal').modal('hide');
          },
          resetCompetitors: function() {
            if (confirm('Do you want to reset competitors?')) {
              ws.send({
                type: 'result',
                action: 'reset',
                round: {
                  event: state.params.e,
                  id: state.params.r,
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
              this.current = JSON.parse(JSON.stringify(result));
              this.$nextTick(function() {
                $('.input-panel-result input').eq(0).focus();
              });
            }
          },
          changeParams: function() {
            store.dispatch('CHANGE_PARAMS', {
              e: this.eventRound.e,
              r: this.eventRound.r,
              filter: this.filter
            });
            if (Date.now() - lastFetchRoundsTime > 300000) {
              ws.send({
                type: 'result',
                action: 'rounds',
              });
            }
          },
          isAdvanced: function(result) {
            if (this.filter != 'all') {
              return false;
            }
            if (result.r == 'c' || result.r == 'f') {
              return result.b > 0 && result.p <= 3;
            }
            var i;
            for (i = 0; i < events[result.e].rs.length; i++) {
              if (events[result.e].rs[i].id == result.r) {
                if (events[result.e].rs[i + 1]) {
                  return result.b > 0 && result.p <= events[result.e].rs[i + 1].n;
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
                v: [0, 0, 0, 0, 0],
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
                    var temp = getUser(resA.n).name.toLowerCase().indexOf(searchText) - getUser(resB.n).name.toLowerCase().indexOf(searchText);
                    if (temp == 0) {
                      temp = getUser(resA.n).name < getUser(resB.n).name ? -1 : 1;
                    }
                    return temp;
                  }
                  return resA.n - resB.n
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
                that.competitor = getUser(result.n);
                that.v = result.v;
                that.searchText = result.n || '';
              },
              searchText: function(searchText) {
                this.selectedIndex = 0;
                if (searchText == '') {
                  this.result = {
                    v: []
                  };
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
                if (!result || !result.i || !this.$parent.isCurrentRoundOpen) {
                  return true;
                }
                var round = eventRounds[state.params.e][state.params.r];
                if (round.co > 0) {
                  var num = round.f == 'a' ? 2 : 1;
                  var passed = false;
                  for (var i = 0; i < num; i++) {
                    if (that.result.v[i] > 0 && that.result.v[i] / 100 < round.co) {
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
                var result = that.result;
                ws.send({
                  type: 'result',
                  action: 'update',
                  result: {
                    id: result.i,
                    value1: result.v[0],
                    value2: result.v[1],
                    value3: result.v[2],
                    value4: result.v[3],
                    value5: result.v[4],
                    best: result.b,
                    average: result.a
                  }
                });
                that.result = {
                  v: []
                };
                $('#input-panel-name').focus();
              },
              filterCompetitors: function(result) {
                var that = this;
                var searchText = that.searchText.trim();
                if (searchText == '') {
                  return false;
                }
                if (/^\d+$/.test(searchText)) {
                  return !!result.n.toString().match(searchText);
                }
                return !!getUser(result.n).name.match(new RegExp(searchText, 'i'));
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
                this.name = result.n;
                this.$parent.edit(result);
              }
            },
            vuex: {
              getters: {
                inputNum: function(state) {
                  var params = state.params;
                  var round = getRound(params);
                  var f = round && round.f;
                  switch (f) {
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
                  var round = getRound(params);
                  var f = round && round.f;
                  switch (f) {
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
                  '$parent.result.i': function() {
                    var that = this;
                    var time = decodeResult(that.value, that.e);
                    if (that.e === '333mbf') {
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
                    var round = getRound(that);
                    that.value = encodeResult(that.formatTime(that.time), that.e, false, that.tried, that.solved);
                    if (round.tl > 0 && that.e != '333mbf' && that.e != '333fm' && that.value / 100 > round.tl) {
                      that.time = 'DNF';
                    }
                    if (that.e === '333fm' && that.value > 80) {
                      that.time = 'DNF';
                    }
                  },
                  formatTime: function(time) {
                    if (time == 'DNF' || time == 'DNS' || time == '' || this.e == '333fm') {
                      return time;
                    }
                    var minute = time.length > 4 ? parseInt(time.slice(0, -4)) : 0;
                    var second = time.length > 2 ? parseInt(time.slice(0, -2).slice(-2)) : 0;
                    var msecond = parseInt(time.slice(-2));
                    if (this.e === '333mbf') {
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
                    var isSameInput = that.$parent.lastIndex == that.$parent.currentIndex;
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
                        if (!isSameInput || that.time == 'DNF' || that.time == 'DNS') {
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
                        if (value.length >= 6 && isSameInput) {
                          break;
                        }
                        if (!isSameInput || that.time == 'DNF' || that.time == 'DNS') {
                          if (that.time === 'DNF' || that.time === 'DNS') {
                            that.solved = that.tried = that.time = '';
                          }
                          value = '';
                        }
                        if ((that.e === '333fm' || that.subIndex < 2) && value.length >= 2) {
                          break;
                        }
                        if (that.e === '333mbf' && value.length >= 4) {
                          break;
                        }
                        value += code - 48;
                        that[attr] = value;
                        if (!isSameInput) {
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
    '/event/:e/:r/:filter': {
      component: {}
    }
  });
  store.watch(function(state) {
    return state.params;
  }, function(params) {
    router.go(['/event', params.e, params.r, params.filter].join('/'));
    fetchResults();
  }, {
    deep: true,
    sync: true,
    // immediate: true
  });
  router.afterEach(function(transition) {
    var params = transition.to.params;
    if (params.e == state.params.e && params.r == state.params.r && params.filter == state.params.filter) {
      return;
    }
    store.dispatch('CHANGE_PARAMS', params);
  });
  router.redirect({
    '*': ['/event', current.e, current.r, state.params.filter].join('/')
  });
  router.start(vm, liveContainer.get(0));

  var newMessage = function() {
    var container = $('.message-container');
    var ul = container.find('ul');
    return function(message, scroll) {
      if (container.length == 0) {
        container = $('.message-container');
        ul = container.find('ul');
      }
      message.type = message.type || 'normal';
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
        type: 'result',
        user: {
          name: 'System'
        },
        time: Math.floor(+new Date() / 1000),
        result: result
      };
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
      params: {
        event: state.params.e,
        round: state.params.r,
        filter: state.params.filter
      }
    });
  }
  function getEvent(result) {
    return events[result.e];
  }
  function getRound(result) {
    return eventRounds[result.e][result.r];
  }
  function getUser(number) {
    return allUsers[number] || {};
  }
  function calculateAverage(result) {
    var best = 999999999;
    var worst = 0;
    var hasAverage = true;
    var i, value, DNFCount = 0, zeroCount = 0, sum = 0;
    var round = getRound(result);
    var f = round.f;
    var num = f == 'a' || f == '' ? 5 : (f == 'm' ? 3 : parseInt(f));
    for (i = 0; i < num; i++) {
      value = result.v[i];
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
    result.b = best;
    //check best
    if (result.b === 999999999) {
      result.b = worst == 0 ? 0 : -1;
    }
    if (f == '') {
      hasAverage = false;
    }
    //
    if ((f == 'a' || f == 'm') && zeroCount > 0) {
      hasAverage = false;
    }
    if (DNFCount > 1 || (DNFCount == 1 && (f == 'm' || f == '3'))) {
      hasAverage = false;
    }
    if (f == '1' || f == '2' || (f == '3' && result.e != '333bf')) {
      hasAverage = false;
    }
    if (hasAverage) {
      if (f == 'm' || f == '3') {
        if (result.e === '333fm') {
          sum *= 100;
        }
        result.a = Math.round(sum / 3);
      } else {
        result.a = Math.round((sum - best - worst) / 3);
      }
      if (result.a / 100 > 600) {
        result.a = Math.round(result.a / 100) * 100;
      }
    } else if (f == 'm' || f == 'a') {
      result.a = zeroCount > 0 ? 0 : -1;
    } else if (result.event == '333bf' || f == '') {
      result.a = 0;
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
      //above 10 minutes
      if (minute * 60 + second > 600) {
        second += msecond > 50 ? 1 : 0;
        msecond = 0;
      }
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
  function calculatePos(results, result) {
    for (var i = 0, len = results.length; i < len; i++) {
      if (!results[i - 1] || compare(results[i - 1], results[i], true) < 0) {
        results[i].p = i + 1;
      } else {
        results[i].p = results[i - 1].p;
      }
      if (results[i].b == 0) {
        results[i].p = '-';
      }
      results[i].isNew = results[i] === result;
    }
  }
  function compare(resA, resB, onlyResult) {
    var temp = 0;
    var round = getRound(resA);
    if (round.f == 'm' || round.f == 'a') {
      if (resA.a > 0 && resB.a <= 0) {
        return -1
      }
      if (resB.a > 0 && resA.a <= 0) {
        return 1
      }
      temp = resA.a - resB.a;
    }
    if (temp == 0) {
      if (resA.b > 0 && resB.b <= 0) {
        return -1
      }
      if (resB.b > 0 && resA.b <= 0) {
        return 1
      }
      temp = resA.b - resB.b;
    }
    if (!onlyResult && temp == 0) {
      temp = getUser(resA.n).name < getUser(resB.n).name ? -1 : 1;
    }
    return temp;
  }
})(this);