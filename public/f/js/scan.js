(function(global) {
  Vue.config.debug = true;
  var vm = window.vm = new Vue({
    data: {
      loading: true,
      registration: {}
    },
    el: '#scan-container',
    watch: {
    },
    methods: {
      scan: function() {
        var that = this;
        that.registration = {};
        wx.ready(function() {
          wx.scanQRCode({
            needResult: 1,
            scanType: ["qrCode"],
            success: function (res) {
              var result = res.resultStr;
              var code = result.split('code=').reverse()[0];
              that.loading = true;
              $.ajax({
                data: {
                  code: code
                },
                dataType: 'json',
                type: 'post',
                success: function(res) {
                  if (res.status == 0) {
                    that.registration = res.data;
                  }
                },
                complete: function() {
                  that.loading = false;
                }
              })
              console.log(code);
            }
          });
        });
      },
      do: function(action) {
        var that = this;
        that.loading = true;
        $.ajax({
          data: {
            id: that.registration.id,
            action: action
          },
          dataType: 'json',
          type: 'post',
          success: function(res) {
            if (res.status == 0) {
              that.registration = res.data;
            }
          },
          complete: function() {
            that.loading = false;
          }
        })
      }
    },
    components: {
    }
  });
  wx.ready(function() {
    vm.loading = false;
  })
})(this);