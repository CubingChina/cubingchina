$(function() {
  $('.tips').tooltip();
  $(document).on('click', 'a.delete', function() {
    if (!confirm('真的真的要删除么？')) {
      return false;
    }
  }).on('click', '.toggle', function() {
    var that = $(this);
    var options = that.data();
    options.values = options.values || [0, 1];
    $.ajax({
      url: options.url,
      type: 'post',
      dataType: 'json',
      data: {
        id: options.id,
        attribute: options.attribute,
        value: options.values[0] == options.value ? options.values[1] : options.values[0]
      },
      timeout: 10000,
      success: function(data) {
        $.extend(options, data.data);
        var index = options.values.indexOf(data.data.value);
        if (index < 0) {
          index = 0;
        }
        if (that.is('input')) {
          that.prop('checked', Boolean(options.value));
        } else {
          options.btns = options.btns || ['green', 'red'];
          that.html(options.text[index]);
          that.removeClass('btn-' + options.btns[1 - index]).addClass('btn-' + options.btns[index]);
        }
      },
      error: function(xhr, textStatus, error) {
        var msg = [];
        switch (textStatus) {
          case 'timeout':
            msg.push('连接超时，请检查网络');
            break;
          default:
            msg.push(error);
            msg.push(xhr.responseText);
        }
        if (options.name) {
          msg.push('涉及数据：' + options.name);
        }
        alert(msg.join('\n'));
      }
    });
    return false;
  }).on('click', '.confirm', function() {
    var that = $(this);
    var options = that.data();
    if (!confirm('请确保比赛信息和申请资料均已详实填写，确认后将不可更改！')) {
      return false;
    }
    $.ajax({
      url: options.url,
      type: 'post',
      dataType: 'json',
      data: {
        id: options.id,
      },
      timeout: 10000,
      success: function(data) {
        if (data.status !==  0) {
          alert(data.msg);
          return
        }
        alert('比赛已提交,请等待管理员/代表审核!');
        location.href = location.href;
      },
      error: function(xhr, textStatus, error) {
        var msg = [];
        switch (textStatus) {
          case 'timeout':
            msg.push('连接超时，请检查网络');
            break;
          default:
            msg.push(error);
            msg.push(xhr.responseText);
        }
        if (options.name) {
          msg.push('涉及数据：' + options.name);
        }
        alert(msg.join('\n'));
      }
    });
    return false;
  });
  function adjustTableContainer() {
    var tableContainer = $('.table-responsive');
    if (tableContainer.length) {
      if (!('ontouchstart' in window) && $(window).height() - tableContainer.offset().top > 475) {
        tableContainer.css({
          'max-height': $(window).height() - tableContainer.offset().top - 60
        });
      } else {
        tableContainer.css({
          'max-height': 'auto'
        });
      }
    }
  }
  adjustTableContainer();
  $(window).on('resize', adjustTableContainer);
})
