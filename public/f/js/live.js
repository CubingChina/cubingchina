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
    fetchResults();
  }).on('result.new', function(result) {
    store.dispatch('NEW_RESULT', result);
    newMessageOnResult(result, 'new');
  }).on('result.update', function(result) {
    store.dispatch('UPDATE_RESULT', result);
    newMessageOnResult(result, 'update');
  }).on('round.update', function(round) {
    store.dispatch('UPDATE_ROUND', round);
  }).on('message.new', function(message) {
    if (state.options.showMessage) {
      newMessage(message);
    }
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
    messages: [],
    options: {
      enableEntry: true,
      showMessage: true,
      alertResult: true,
      alertRecord: true
    }
  };
  var events = {};
  var eventRounds = {};
  var current = {}
  var mutations = {
    CHANGE_EVENT_ROUND: function(state, params) {
      state.params = params;
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
    eventRounds[event.id] = {};
    event.rounds.forEach(function(round) {
      eventRounds[event.id][round.id] = round;
      if (!current.event && round.status == 2) {
        current.event = event.id;
        current.round = round.id;
      }
    });
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
    data: function() {
      return {
      };
    },
    vuex: {
      getters: {
        options: function(state) {
          return state.options;
        },
        hasPermission: function(state) {
          var user = state.user;
          return user.isOrganizer || user.isDelegate || user.isAdmin;
        }
      }
    },
    methods: {
      showOptions: function() {
        $('#options-modal').modal();
      }
    },
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
            eventRound: null,
            current: null
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
            options: function(state) {
              return state.options;
            },
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
          goToUser: function(user) {
            console.log(user)
          },
          edit: function(result) {
            if (this.hasPermission) {
              this.current = result;
              this.$nextTick(function() {
                $('.input-panel-result input').eq(0).focus();
              });
            }
          },
          changeEventRound: function() {
            store.dispatch('CHANGE_EVENT_ROUND', {
              event: this.eventRound.event,
              round: this.eventRound.round
            });
          },
          isAdvanced: function(result) {
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
        filters: {
          decodeResult: function(result, event) {
            return decodeResult(result, event);
          }
        },
        components: {
          'input-panel': {
            props: ['result'],
            data: function() {
              return {
                lastInput: null,
                input: null,
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
                name: '',
                searching: false,
                selectedIndex: 0
              }
            },
            computed: {
              competitors: function() {
                return this.results.filter(this.filterCompetitors.bind(this)).sort(function(resA, resB) {
                  return resA.number - resB.number
                }).slice(0, 5)
              }
            },
            watch: {
              result: function(result) {
                var that = this;
                that.competitor = result.user;
                that.value1 = result.value1 || 0;
                that.value2 = result.value2 || 0;
                that.value3 = result.value3 || 0;
                that.value4 = result.value4 || 0;
                that.value5 = result.value5 || 0;
              },
              name: function(name) {
                this.selectedIndex = 0;
                if (name == '') {
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
                if (!result || !result.id) {
                  return true;
                }
                var round = eventRounds[state.params.event][state.params.round];
                if (round.cut_off > 0) {
                  var num = round.format == 'a' ? 2 : 1;
                  var passed = false;
                  for (var i = 1; i <= num; i++) {
                    if (that['value' + i] / 100 < round.cut_off) {
                      passed = true;
                      break;
                    }
                  }
                  return !(passed || index < num);
                }
                return false;
              },
              save: function() {
                var that = this;
                that.result.value1 = that.value1;
                that.result.value2 = that.value2;
                that.result.value3 = that.value3;
                that.result.value4 = that.value4;
                that.result.value5 = that.value5;
                calculateAverage(that.result);
                store.dispatch('UPDATE_RESULT', that.result);
                ws.send({
                  type: 'result',
                  action: 'update',
                  result: that.result
                });
                that.result = {};
              },
              filterCompetitors: function(result) {
                var that = this;
                var name = that.name.trim();
                if (name == '') {
                  return false;
                }
                if (/^\d+$/.test(name)) {
                  return !!result.number.toString().match(name);
                }
                return !!result.user.name.match(new RegExp(name, 'i'));
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
                this.$parent.edit(result);
                this.name = result.number;
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
                },
                results: function(state) {
                  return state.results;
                }
              }
            },
            filters: {
              result: {
                read: function(value) {
                  return decodeResult(value, state.params.event);
                },
                write: function(value) {
                  return encodeResult(value, state.params.event);
                }
              }
            },
            template: '#input-panel-template',
            components: {
              'result-input': {
                props: ['value', 'index'],
                data: function() {
                  return {
                    display: '',
                  }
                },
                watch: {
                  display: function(display, oldDisplay) {
                    if (display != oldDisplay) {
                      this.value = encodeResult(display, this.$store.state.params.event);
                    }
                  },
                  value: function(value, oldValue) {
                    if (value != oldValue) {
                      this.display = decodeResult(value, this.$store.state.params.event);
                    }
                  }
                },
                methods: {
                  formatResult: function(value) {
                    if (value == 'DNF' || value == 'DNS' || value == '') {
                      return value;
                    }
                    var match = value.match(/(?:(\d+)?:)?(?:(\d{1,2})\.)?(\d{1,})?/);
                    var minute = match[1] ? parseInt(match[1]) : 0;
                    var second = match[2] ? parseInt(match[2]) : 0;
                    var msecond = match[3] ? parseInt(match[3]) * (match[3].length == 1 ? 10 : 1) : 0;
                    return decodeResult((minute * 60 + second) * 100 + msecond, state.params.event);
                  },
                  focus: function(e, name) {
                    this.$parent.input = name;
                  },
                  blur: function(e) {
                    e.target.value = this.formatResult(e.target.value);
                    this.value = encodeResult(e.target.value);
                    this.$parent.lastInput = null;
                  },
                  keydown: function(e) {
                    var code = e.which;
                    var value = e.target.value;
                    switch (code) {
                      //D,/ pressed
                      case 68:
                      case 111:
                        this.value = -1;
                        e.target.value = 'DNF'
                        break;
                      //S,* pressed
                      case 106:
                      case 83:
                        this.value = -2;
                        e.target.value = 'DNS'
                        break;
                      case 8:
                      case 109:
                        if (this.lastInput != this.input) {
                          this.value = 0;
                          e.target.value = '';
                          this.$parent.lastInput = this.$parent.input;
                        } else {
                          value = value.replace(/^0./, '');
                          value = value.replace(/:|\./g, '');
                          if (this.$parent.lastInput != this.$parent.input || value == 'DNF' || value == 'DNS') {
                            value = '';
                          }
                          value = value.slice(0, value.length - 1);
                          switch (value.length) {
                            case 1:
                            case 2:
                              break;
                            case 3:
                              value = value.charAt(0) + '.' + value.charAt(1) + value.charAt(2);
                              break;
                            case 4:
                              value = value.charAt(0) + value.charAt(1) + '.' + value.charAt(2) + value.charAt(3);
                              break;
                            case 5:
                              value = value.charAt(0) + ':' + value.charAt(1) + value.charAt(2) + '.' + value.charAt(3) + value.charAt(4);
                              break;
                            case 6:
                              value = value.charAt(0) + value.charAt(1) + ':' + value.charAt(2) + value.charAt(3) + '.' + value.charAt(4) + value.charAt(5);
                              break;
                          }
                          e.target.value = value;
                        }
                        break;
                      case 107:
                      case 9:
                        if (e.shiftKey || code == 107) {
                          var that = $(e.target).parent();
                          var index = that.index();
                          if (index > 0) {
                            that.prev().find('input').focus();
                          }
                          break;
                        }
                      case 13:
                        var that = $(e.target).parent();
                        var index = that.index();
                        if (index < this.$parent.inputNum - 1) {
                          that.next().find('input').focus();
                        } else {
                          that.parent().next().focus();
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
                        if (value.length >= 8) {
                          break;
                        }
                        value = value.replace(/^0./, '');
                        value = value.replace(/:|\./g, '');
                        if (this.$parent.lastInput != this.$parent.input || value == 'DNF' || value == 'DNS') {
                          value = '';
                        }
                        value += code - 48;
                        switch (value.length) {
                          case 1:
                          case 2:
                            break;
                          case 3:
                            value = value.charAt(0) + '.' + value.charAt(1) + value.charAt(2);
                            break;
                          case 4:
                            value = value.charAt(0) + value.charAt(1) + '.' + value.charAt(2) + value.charAt(3);
                            break;
                          case 5:
                            value = value.charAt(0) + ':' + value.charAt(1) + value.charAt(2) + '.' + value.charAt(3) + value.charAt(4);
                            break;
                          case 6:
                            value = value.charAt(0) + value.charAt(1) + ':' + value.charAt(2) + value.charAt(3) + '.' + value.charAt(4) + value.charAt(5);
                            break;
                        }
                        e.target.value = value;
                        if (this.$parent.lastInput != this.$parent.input) {
                          this.$parent.lastInput = this.$parent.input;
                        }
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
    '/event/:event/:round': {
      component: {}
    }
  });
  store.watch(function(state) {
    return state.params;
  }, function(params) {
    router.go(['/event', params.event, params.round].join('/'));
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
    '*': ['/event', current.event || state.params.event, current.round || state.params.round].join('/')
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
    if (state.options.alertResult) {
      var message = {
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
      var num = result.format == 'a' ? 5 : (result.format == '3' ? 3 : parseInt(result.format));
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
    if (state.options.alertRecord && (result.regional_single_record != '' || result.regional_average_record != '')) {
      //@todo alert record
    }
  }
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
  function calculateAverage(result) {
    var best = 999999999;
    var worst = 0;
    var hasAverage = true;
    var i, value, DNFCount = 0, zeroCount = 0, sum = 0;
    var num = result.format == 'a' ? 5 : (result.format == '3' ? 3 : parseInt(result.format));
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

    } else {
      var match = result.match(/(?:(\d+)?:)?(\d{1,2})\.(\d{1,2})/);
      if (!match) {
        return 0;
      }
      var minute = match[1] ? parseInt(match[1]) : 0;
      var second = parseInt(match[2]);
      var msecond = parseInt(match[3]) * (match[3].length == 1 ? 10 : 1);
      return (minute * 60 + second) * 100 + msecond;
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