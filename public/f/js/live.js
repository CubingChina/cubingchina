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
    event: '',
    round: '',
    results: [],
    messages: []
  };
  var mutations = {
    NEW_RESULT: function(state, result) {
      if (1 || result.competitionId == state.competitionId && result.event == state.event && result.round == state.round) {
        result.pos = '';
        result.isNew = true;
        var results = state.results;
        var key = findKey(results, result);
        results.splice(key, 0, result);
        var i = 0, len = results.length;
        for (; i < len; i++) {
          if (!results[i - 1] || compare(results[i - 1], results[i]) < 0) {
            results[i].pos = i + 1;
          } else {
            results[i].pos = results[i - 1].pos;
          }
          results[i].isNew = results[i] === result;
        }
        state.results = results;
      }
    },
    NEW_MESSAGE: function(state, message) {
      state.messages.push(message);
      if (state.messages.length > 10) {
        state.messages.splice(0, 1);
      }
    }
  };
  var store = window.store = new Vuex.Store({
    state: state,
    mutations: mutations
  });
  var vm = new Vue({
    el: liveContainer.get(0),
    data: liveContainer.data(),
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
            store.dispatch('NEW_MESSAGE', {
              text: that.message
            })
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
        template: $('#result-template').html()
      })
    }
  });
  var ws = new WS('ws://' + location.host + '/ws');
  ws.on('connect', function() {
    this.send({
      type: 'competition',
      competitionId: vm.competitionId
    });
  }).on('newresult', function(result) {
    store.dispatch('NEW_RESULT', result);
  }).on('newmessage', function(message) {
    store.dispatch('NEW_MESSAGE', message);
  });

  function findKey(results, result) {
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