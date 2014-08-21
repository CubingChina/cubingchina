<!DOCTYPE html>
<html xmlns:wb="http://open.weibo.com/wb" lang="en" class="<?php echo $this->IEClass; ?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
  <link rel="icon" sizes="196x196" href="/f/images/icon196.png"> 
  <link rel="apple-touch-icon" href="/f/images/icon196.png">
  <link rel="apple-touch-icon-precomposed" sizes="128x128" href="/f/images/icon196.png">
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo CHtml::normalizeUrl(array('/feed/index')); ?>" />
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="320">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo CHtml::encode($this->description); ?>">
  <meta name="keywords" content="<?php echo CHtml::encode($this->keywords); ?>">
  <meta name="author" content="<?php echo Yii::app()->params->author; ?>">
  <meta property="wb:webmaster" content="c86bdd248e65def9" />
  <!-- Theme CSS -->
  <?php if (DEV): ?>
  <link id="theme-style" rel="stylesheet" href="/f/plugins/bootstrap/css/bootstrap.min.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <link id="theme-style" rel="stylesheet" href="/f/plugins/font-awesome/css/font-awesome.min.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <link id="theme-style" rel="stylesheet" href="/f/css/styles.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <?php else: ?>
  <link id="theme-style" rel="stylesheet" href="/f/css/main.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <?php endif; ?>
  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="/f/js/html5shiv.js"></script>
    <script src="/f/js/respond.min.js"></script>
  <![endif]-->
</head>
<body class="<?php echo $this->id; ?> <?php echo $this->id; ?>-<?php echo $this->action->id; ?>">
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
  <footer class="footer">
    <div class="bottom-bar">
      <div class="container">
        <div class="row">
          <small class="copyright col-md-6 col-sm-12 col-xs-12">Copyright @ <?php echo date('Y'); ?> <?php echo Yii::t('common', Yii::app()->name); ?> 京ICP备14025871号</small>
          <ul class="social pull-right col-md-6 col-sm-12 col-xs-12">
            <li class="row-end"><?php echo CHtml::link('<i class="fa fa-rss"></i>', array('/feed/index')); ?></li>
          </ul>
        </div><!--//row-->
      </div><!--//container-->
    </div><!--//bottom-bar-->
  </footer><!--//footer-->
  <!-- Javascript -->
  <script type="text/javascript" src="/f/js/main.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>