<div class="row">
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/user/index'); ?>">
        <div class="circle-tile-heading dark-blue">
          <i class="fa fa-users fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content dark-blue">
        <div class="circle-tile-description text-faded">
          注册用户
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $totalUser; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/user/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/user/index'); ?>">
        <div class="circle-tile-heading green">
          <i class="fa fa-user fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content green">
        <div class="circle-tile-description text-faded">
          平均每天注册用户
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $userPerDay; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/user/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/user/index'); ?>">
        <div class="circle-tile-heading purple">
          <i class="fa fa-users fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content purple">
        <div class="circle-tile-description text-faded">
          高级用户
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $advancedUser; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/user/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/user/index'); ?>">
        <div class="circle-tile-heading red">
          <i class="fa fa-users fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content red">
        <div class="circle-tile-description text-faded">
          未激活用户
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $uncheckedUser; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/user/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/registration/index'); ?>">
        <div class="circle-tile-heading blue">
          <i class="fa fa-user fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content blue">
        <div class="circle-tile-description text-faded">
          报名人次
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $totalRegistration; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/registration/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-2 col-sm-6">
    <div class="circle-tile">
      <a href="<?php echo $this->createUrl('/board/registration/index'); ?>">
        <div class="circle-tile-heading orange">
          <i class="fa fa-user fa-fw fa-3x"></i>
        </div>
      </a>
      <div class="circle-tile-content orange">
        <div class="circle-tile-description text-faded">
          通过的报名人次
        </div>
        <div class="circle-tile-number text-faded">
          <?php echo $acceptedRegistration; ?>
        </div>
        <a href="<?php echo $this->createUrl('/board/registration/index'); ?>" class="circle-tile-footer">查看 <i class="fa fa-chevron-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="col-lg-12">
    <div class="portlet portlet-green">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>每天注册/报名用户统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#dailyUser"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="dailyUser" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="daily-user"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-12">
    <div class="portlet portlet-blue">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>每小时注册用户/报名比赛统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#hourlyStat"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="hourlyStat" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="hourly-stat"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="portlet portlet-orange">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>注册用户地区统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#userRegion"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="userRegion" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="user-region"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="portlet portlet-purple">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>注册用户性别统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#userGender"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="userGender" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="user-gender"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="portlet portlet-red">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>注册用户是否参加WCA统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#userWca"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="userWca" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="user-wca"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>注册用户年龄统计</h4>
        </div>
        <div class="portlet-widgets">
          <span class="divider"></span>
          <a data-toggle="collapse" href="#userAge"><i class="fa fa-chevron-down"></i></a>
        </div>
        <div class="clearfix"></div>
      </div>
      <div id="userAge" class="panel-collapse collapse in">
        <div class="portlet-body">
          <div id="user-age"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
Yii::app()->clientScript->registerCssFile('/b/css/plugins/morris/morris.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/morris/raphael-2.1.0.min.js');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/morris/morris.js');
$dailyKeys = array_keys($dailyData[0]);
$dailyKeys = json_encode(array_slice($dailyKeys, 1));
$dailyData = json_encode($dailyData);
$hourlyKeys = array_keys($hourlyData[0]);
$hourlyKeys = json_encode(array_slice($hourlyKeys, 1));
$hourlyData = json_encode($hourlyData);
$userRegion = json_encode($userRegion);
$userGender = json_encode($userGender);
$userAge = json_encode($userAge);
$userWca = json_encode($userWca);
Yii::app()->clientScript->registerScript('statistics',
<<<EOT
  Morris.Line({
    element: 'daily-user',
    data: {$dailyData},
    xkey: 'day',
    ykeys: {$dailyKeys},
    lineColors: ['#16a085', '#f39c12'],
    labels: ['注册数', '报名数'],
    smooth: false,
    resize: true
  });
  Morris.Bar({
    element: 'hourly-stat',
    data: {$hourlyData},
    xkey: 'hour',
    ykeys: {$hourlyKeys},
    labels: ['注册数', '报名数'],
    barColors: ['#8e44ad', '#e74c3c'],
    resize: true
  });
  Morris.Donut({
    element: 'user-region',
    data: {$userRegion},
    resize: true,
    colors: ['#16a085', '#2980b9', '#f39c12', '#e74c3c', '#8e44ad', '#1c92c7', '#34495e'],
    formatter: function(y) {
        return y;
    }
  });
  Morris.Donut({
    element: 'user-gender',
    data: {$userGender},
    resize: true,
    colors: ['#2980b9', '#e74c3c', '#8e44ad', '#1c92c7', '#34495e'],
    formatter: function(y) {
        return y;
    }
  });
  Morris.Donut({
    element: 'user-wca',
    data: {$userWca},
    resize: true,
    colors: ['#2980b9', '#e74c3c', '#8e44ad', '#1c92c7', '#34495e'],
    formatter: function(y) {
        return y;
    }
  });
  Morris.Bar({
    element: 'user-age',
    data: {$userAge},
    xkey: 'age',
    ykeys: ['count'],
    lineColors: ['#f39c12'],
    labels: ['人数'],
    smooth: false,
    resize: true
  });
EOT
);