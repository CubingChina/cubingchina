(function(global) {
  var vm = window.vm = new Vue({
    data: {
      mode: 'pc',
      loading: true,
      scanning: false,
      url: '',
      competitionId: '',
      registration: {}
    },
    el: '#scan-container',
    ready: function() {
      this.competitionId = this.$el.dataset.competitionId;
      // console.log(111)
    },
    methods: {
      check: function() {
        this.fetchInfo(this.url);
        this.url = '';
      },
      startScan: function() {
        this.scanning = true;
      },
      endScan: function() {
        this.scanning = false;
        this.focus();
      },
      focus: function() {
        this.$els.urlInput.focus();
      },
      scan: function() {
        var that = this;
        that.registration = {};
        wx.ready(function() {
          wx.scanQRCode({
            needResult: 1,
            scanType: ["qrCode"],
            success: function (res) {
              var result = res.resultStr;
              that.fetchInfo(result);
            }
          });
        });
      },
      fetchInfo: function(url) {
        var code = url.split('code=').reverse()[0];
        if (!code) {
          return;
        }
        var that = this;
        that.loading = true;
        $.ajax({
          url: '/api/competition/competitor',
          data: {
            competition_id: that.competitionId,
            code: code,
          },
          dataType: 'json',
          success: function(res) {
            if (res.status == 0) {
              that.registration = res.data;
            } else {
              alert(res.message);
              that.registration = {};
            }
          },
          complete: function() {
            that.loading = false;
          }
        })
      },
      doAction: function(action) {
        var that = this;
        that.loading = true;
        $.ajax({
          url: '/api/competition/signin',
          data: {
            id: that.registration.id,
            action: action
          },
          dataType: 'json',
          type: 'post',
          success: function(res) {
            if (res.status == 0) {
              that.registration = res.data;
            } else {
              alert(res.message);
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
    vm.mode = 'wx';
  })
  if (/MicroMessenger/i.test(navigator.userAgent) === false) {
    vm.loading = false;
    vm.mode = 'pc';
    vm.focus();
  }
})(this);
