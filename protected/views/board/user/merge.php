<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>合并用户</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>合并用户</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <div class="well">
            <p>将用户2与用户1合并，用户2的报名等数据会转移到用户1上；<br>输入姓名、ID等查询用户。</p>
          </div>
          <form>
            <div class="form-group">
              <label for="user1">用户1</label>
              <input type="text" id="user1" class="user">
            </div>
            <div class="form-group">
              <label for="user2">用户2</label>
              <input type="text" id="user2" class="user">
            </div>
            <button type="button" class="btn btn-default btn-square" id="merge">合并</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerPackage('tagsinput');
Yii::app()->clientScript->registerScript('merge',
<<<EOT
  $(document).on('click', '#merge', function() {
    var users = $('.user').tagsinput('items').filter(function(items) {
      return items.length > 0;
    }).map(function(user) {
      return user[0].id
    });
    if (users.length < 2) {
      return;
    }
    $.ajax({
      type: 'post',
      data: {
        users: users
      },
      dataType: 'json',
      success: function(result) {
        if (result.status == 0) {
          alert('Merge success!');
          $('.user').tagsinput('removeAll');
        } else {
          alert(result.message);
        }
      }
    })
  });
  $('.user').on('itemAdded', function() {
    var that = $(this);
    setTimeout(function() {
      that.tagsinput('input').val('');
    }, 0);
  }).tagsinput({
    itemValue: function(user) {
      return [user.id, user.display_name].join('-')
    },
    maxTags: 1,
    freeInput: false,
    typeahead: {
      source: function(query) {
        return $.ajax({
          url: '/board/user/search',
          data: {
            query: query
          },
          dataType: 'json'
        })
      }
    }
  })
EOT
);
