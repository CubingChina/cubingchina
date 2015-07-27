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
          that.text(options.text[index]);
          that.removeClass('btn-' + options.btns[1 - index]).addClass('btn-' + options.btns[index]);
        }
      },
      error: function() {
        alert('操作失败，请联系管理员');
      }
    })
  });
  function adjustTableContainer() {
    var tableContainer = $('.table-responsive');
    if (tableContainer.length) {
      tableContainer.css({
        'max-height': $(window).height() - tableContainer.offset().top - 60
      })
    }
  }
  adjustTableContainer();
  $(window).on('resize', adjustTableContainer);
})