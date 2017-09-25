<nav class="main-nav<?php if ($this->controller->id == 'competition' || $this->controller->id == 'live') echo ' colorful'; ?>" role="navigation">
  <div class="container">
    <div class="navbar-header" data-toggle="collapse" data-target="#navbar-collapse">
      <button class="navbar-toggle" type="button">
        <?php echo Yii::t('common', 'Menu'); ?>
      </button><!--//nav-toggle-->
    </div><!--//navbar-header-->
    <div class="navbar-collapse collapse" id="navbar-collapse">
      <?php $this->widget('zii.widgets.CMenu', array(
        'encodeLabel'=>false,
        'htmlOptions'=>array(
          'class'=>'nav navbar-nav ' . $this->controller->id . ' ' . $this->controller->id . '-' . $this->controller->action->id,
        ),
        'submenuHtmlOptions'=>array(
          'class'=>'dropdown-menu',
        ),
        'items'=>$this->controller->navibar,
      ));?>
    </div><!--//navabr-collapse-->
  </div><!--//container-->
</nav><!--//main-nav-->
