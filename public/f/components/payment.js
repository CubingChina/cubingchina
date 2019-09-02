import utils from '../utils'

const inWechat = navigator.userAgent.match(/MicroMessenger/i)
const paymentId = $('#payment-id').val()
let channel = $('#payment-channel').val()

if (paymentId) {
  if (window.name === 'redirected-' + paymentId) {
    showOperations()
    window.name = ''
  }
  if (inWechat) {
    $('.pay-channel[data-channel="balipay"]').remove()
  }
  if (channel) {
    $('.pay-channel[data-channel="' + channel + '"]').addClass('active')
  }
  if ($('.pay-channel.active').length === 0) {
    $('.pay-channel').first().addClass('active')
    channel = $('.pay-channel.active').data('channel')
  }
  $('.pay-channel').on('click', function() {
    channel = $(this).data('channel')
    if (channel) {
      $(this).addClass('active').siblings().removeClass('active')
    }
  })
  $('#pay').on('click', function() {
    $('#pay-tips').removeClass('hide')
    $(this).prop('disabled', true)
    $('.pay-channel').off('click')
    $.ajax({
      url: '/pay/params',
      data: {
        id: paymentId,
        is_mobile: Number('ontouchstart' in window),
        channel: channel
      },
      dataType: 'json',
      success: function(result) {
        const data = result.data
        switch (data.type) {
          case 'paid':
            location.href = data.url
            break
          case 'redirect':
            showOperations()
            window.name = 'redirected-' + paymentId
            location.href = data.url
            break
          case 'scan':
            utils.confirm([
              `<div class="text-center"><img src="${data.src}" /></div>`,
            ], window.wxScanDialogOptions).then(() => {
              checkStatus(paymentId)
            }, refresh)
            break
          case 'wx':
            const config = data.config
            config.success = function(res) {
              if (res.errMsg === 'chooseWXPay:ok') {
                checkStatus(paymentId)
              } else {
                refresh()
              }
            }
            config.cancel = refresh
            wx.chooseWXPay(config)
            break
          case 'form':
            showOperations()
            window.name = 'redirected-' + paymentId
            submitForm(data)
            break
        }
      }
    })
  })
  function submitForm(data) {
    const form = $('<form>').attr({
      action: data.action,
      method: data.method || 'get',
      // target: '_blank',
    })
    for (let k in data.params) {
      $('<input type="hidden">').attr('name', k).val(data.params[k]).appendTo(form)
    }
    form.appendTo(document.body)
    form.submit()
  }
}
function checkStatus(id) {
  $.ajax({
    url: '/pay/check',
    data: {
      id
    },
    type: 'post',
    dataType: 'json',
    success(result) {
      if (result.data && result.data.url) {
        location.href = result.data.url
      }
    }
  })
}
function showOperations() {
  utils.confirm('', window.operationsDialogOptions).then(() => {
    checkStatus(paymentId)
  }, refresh)
}
function refresh() {
  location.href = location
}


