$(function() {
  $('.tips').tooltip();
  $(document).on('click', 'a.delete', function() {
    if (!confirm('真的真的要删除么？')) {
      return false;
    }
  }).on('change', '.paid', function() {
    var that = $(this);
    var url = this.checked ? that.data('checked-url') : that.data('unchecked-url');
    location.href = url;
  });
})