<!DOCTYPE html>
<html xmlns:wb="http://open.weibo.com/wb" lang="en" class="<?php echo $this->IEClass; ?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-siteapp">
  <!-- Theme CSS -->
  <?php if (DEV): ?>
  <link id="theme-style" rel="stylesheet" href="/f/plugins/bootstrap/css/bootstrap.min.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <link id="theme-style" rel="stylesheet" href="/f/plugins/font-awesome/css/font-awesome.min.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <link id="theme-style" rel="stylesheet" href="/f/css/styles.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <?php else: ?>
  <link id="theme-style" rel="stylesheet" href="/f/css/main.css?v=<?php echo Yii::app()->params->cssVer; ?>">
  <?php endif; ?>
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
  <link rel="icon" sizes="196x196" href="/f/images/icon196.png"> 
  <link rel="apple-touch-icon" href="/f/images/icon196.png">
  <link rel="apple-touch-icon-precomposed" sizes="128x128" href="/f/images/icon196.png">
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo CHtml::normalizeUrl(array('/feed/index')); ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="320">
  <meta name="description" content="<?php echo CHtml::encode($this->description); ?>">
  <meta name="keywords" content="<?php echo CHtml::encode($this->keywords); ?>">
  <meta name="author" content="<?php echo Yii::app()->params->author; ?>">
  <meta property="wb:webmaster" content="c86bdd248e65def9">
  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="/f/js/html5shiv.js"></script>
    <script src="/f/js/respond.min.js"></script>
  <![endif]-->
</head>
<body class="<?php echo $this->id; ?> <?php echo $this->id; ?>-<?php echo $this->action->id; ?>">
  <?php echo $content; ?>
  <!-- Javascript -->
  <?php if (DEV): ?>
  <script type="text/javascript" src="/f/plugins/jquery-1.10.2.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script type="text/javascript" src="/f/plugins/bootstrap/js/bootstrap.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script type="text/javascript" src="/f/plugins/bootstrap-hover-dropdown.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script type="text/javascript" src="/f/plugins/back-to-top.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script type="text/javascript" src="/f/plugins/jquery-placeholder/jquery.placeholder.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <script type="text/javascript" src="/f/js/main.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <?php else: ?>
  <script type="text/javascript" src="/f/js/main.min.js?v=<?php echo Yii::app()->params->jsVer; ?>"></script>
  <?php endif; ?>
</body>
</html>