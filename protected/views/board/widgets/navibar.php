<!-- begin TOP NAVIGATION -->
<nav class="navbar-top" role="navigation">

  <!-- begin BRAND HEADING -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle pull-right" data-toggle="collapse" data-target=".sidebar-collapse">
      <i class="fa fa-bars"></i> Menu
    </button>
    <div class="navbar-brand">
      <a href="<?php echo Yii::app()->createUrl('/board'); ?>">
        <img src="/b/img/flex-admin-logo.png" class="img-responsive" alt="">
      </a>
    </div>
  </div>
  <!-- end BRAND HEADING -->

  <div class="nav-top">

    <!-- begin LEFT SIDE WIDGETS -->
    <ul class="nav navbar-left">
      <li class="tooltip-sidebar-toggle">
        <a href="#" id="sidebar-toggle" data-toggle="tooltip" data-placement="right" title="伸缩侧边栏">
          <i class="fa fa-bars"></i>
        </a>
      </li>
      <!-- You may add more widgets here using <li> -->
    </ul>
    <!-- end LEFT SIDE WIDGETS -->
    <!-- begin MESSAGES/ALERTS/TASKS/USER ACTIONS DROPDOWNS -->
    <ul class="nav navbar-right">
      <?php if ($this->controller->alerts !== array()): ?>
      <li class="dropdown">
        <a data-toggle="dropdown" class="alerts-link dropdown-toggle" href="#">
          <i class="fa fa-bell"></i> 
          <span class="number"><?php echo count($this->controller->alerts); ?></span><i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-scroll dropdown-alerts">
          <!-- Alerts Dropdown Heading -->
          <li class="dropdown-header">
            <i class="fa fa-bell"></i> <?php echo count($this->controller->alerts); ?>个提醒
          </li>
          <li>
            <?php $this->widget('zii.widgets.CMenu', array(
              'htmlOptions'=>array(
                'class'=>'list-unstyled',
              ),
              'encodeLabel'=>false,
              'items'=>$this->controller->alerts,
            ));?>
          </li>
        </ul>
        <!-- /.dropdown-menu -->
      </li>
      <?php endif; ?>
      <!-- begin USER ACTIONS DROPDOWN -->
      <li class="dropdown">
        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
          <i class="fa fa-user"></i><i class="fa fa-caret-down"></i>
        </a>
        <?php $this->widget('zii.widgets.CMenu', array(
          'htmlOptions'=>array(
            'class'=>'dropdown-menu dropdown-user',
          ),
          'encodeLabel'=>false,
          'items'=>array(
            array(
              'label'=>'<i class="fa fa-home"></i> 前台',
              'url'=>array('/site/index'),
            ),
            array(
              'label'=>'<i class="fa fa-sign-in"></i> 登录',
              'url'=>array('/site/login'),
              'visible'=>Yii::app()->user->isGuest,
            ),
            array(
              'label'=>'<i class="fa fa-sign-out"></i> 退出',
              'url'=>array('/site/logout'),
              'visible'=>!Yii::app()->user->isGuest,
            ),
          )
        ));?>
      </li>
      <!-- /.dropdown -->
      <!-- end USER ACTIONS DROPDOWN -->
    </ul>
    <!-- /.nav -->
    <!-- end MESSAGES/ALERTS/TASKS/USER ACTIONS DROPDOWNS -->
  </div>
  <!-- /.nav-top -->
</nav>
<!-- /.navbar-top -->
<!-- end TOP NAVIGATION -->