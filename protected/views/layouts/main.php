<?php $this->beginContent('//layouts/simple'); ?>
<div class="wrapper">
  <header class="header">
    <div class="header-main container">
      <h1 class="logo col-md-4 col-sm-4">
          <a href="<?php echo $this->createUrl('/site/index'); ?>"><img id="logo" src="/f/images/logo.png" alt="Logo"></a>
      </h1><!--//logo-->
      <div class="info col-md-8 col-sm-8 hidden-sm hidden-xs">
        <ul class="menu-top navbar-right">
          <li class="divider"><a href="<?php echo $this->getLangUrl('zh_cn'); ?>">简体中文</a></li>
          <li class="divider"><a href="<?php echo $this->getLangUrl('zh_tw'); ?>">繁体中文</a></li>
          <li><a href="<?php echo $this->getLangUrl('en'); ?>">English</a></li>
        </ul>
        <br />
        <div class="contact pull-right">
          <?php if (Yii::app()->user->isGuest): ?>
          <p class="phone"><?php echo CHtml::link('<i class="fa fa-sign-in"></i>' . Yii::t('common', 'Login'), array('/site/login')); ?></p> 
          <p class="email"><?php echo CHtml::link('<i class="fa fa-user"></i>' . Yii::t('common', 'Register'), array('/site/register')); ?></p> 
          <?php else: ?>
          <p class="phone"><?php echo CHtml::link('<i class="fa fa-user"></i>' . $this->user->getAttributeValue('name'), array('/user/profile')); ?></p> 
          <p class="email"><?php echo CHtml::link('<i class="fa fa-sign-out"></i>' . Yii::t('common', 'Logout'), array('/site/logout')); ?></p> 
          <?php endif; ?>
        </div>
      </div><!--//info-->
    </div><!--//header-main-->
  </header><!--//header-->
  <?php $this->widget('Navibar'); ?>

  <div class="content container">
    <div class="page-wrapper">
      <?php if ($this->title != ''): ?>
      <header class="page-heading clearfix">
        <h1 class="heading-title pull-left"><?php echo $this->title; ?></h1>
        <?php $this->renderPartial('/layouts/weiboShare', $_data_); ?>
        <?php $this->widget('Breadcrumbs'); ?>
      </header>
      <?php endif; ?>
      <div class="page-content">
        <div class="row page-row">
          <?php
            $flashes = Yii::app()->user->flashes;
            if (!empty($flashes)):
          ?>
          <div class="col-lg-12">
          <?php foreach ($flashes as $type=>$value): ?>
          <div class="alert alert-<?php echo $type; ?> alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?php echo $value; ?>
          </div>
          <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php echo $content; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->endContent(); ?>