(function(global) {
  if (!('WS' in global)) {
    alert('Your browser doesn\'t support, please upgrade!');
    return;
  }
  Vue.use(Vuex);
  var liveContainer = $('#live-container');
  var state = {

  };
  var mutation = {

  };
  var store = new Vuex.Store({
    state: state,
    mutation: mutation
  });
  var vm = new Vue({
    el: liveContainer.get(0),
    data: liveContainer.data(),
    template: $('#live-container-template').html(),
    components: {
      chat: Vue.extend({
        template: $('#chat-template').html()
      }),
      result: Vue.extend({
        data: function() {
          return {
            results: []
          }
        },
        template: $('#result-template').html()
      })
    }
  });
  var ws = new WS('ws://' + location.host + '/ws');
  ws.send({
    type: 'competition',
    competitionId: vm.competitionId
  });
  ws.on('newResult', function(result) {

  });
})(this);