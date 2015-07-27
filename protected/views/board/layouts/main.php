<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Cubing China">
  <meta name="author" content="Baiqiang Dong">

  <!-- PACE LOAD BAR PLUGIN - This creates the subtle load bar effect at the top of the page. -->
  <link href="/b/css/plugins/pace/pace.css" rel="stylesheet">
  <script src="/b/js/plugins/pace/pace.js"></script>

  <!-- GLOBAL STYLES - Include these on every page. -->
  <link href="/b/css/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/b/icons/font-awesome/css/font-awesome.min.css" rel="stylesheet">

  <!-- THEME STYLES - Include these on every page. -->
  <link href="/b/css/style.css?v=20150727" rel="stylesheet">
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>

  <!--[if lt IE 9]>
    <script src="/b/js/html5shiv.js"></script>
    <script src="/b/js/respond.min.js"></script>
  <![endif]-->
</head>
<body>
  <div id="wrapper">
    <?php $this->widget('Navibar'); ?>
    <?php $this->widget('Sidebar'); ?>
    <!-- begin MAIN PAGE CONTENT -->
    <div id="page-wrapper">
      <div class="page-content">
        <?php foreach (Yii::app()->user->flashes as $type=>$value): ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissable">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <?php echo $value; ?>
        </div>
        <?php endforeach; ?>
        <?php echo $content; ?>
      </div>
      <!-- /.page-content -->
    </div>
    <!-- /#page-wrapper -->
    <!-- end MAIN PAGE CONTENT -->
  </div>
  <!-- GLOBAL SCRIPTS -->
  <script src="/js/jquery.min.js"></script>
  <script src="/b/js/plugins/bootstrap/bootstrap.min.js"></script>
  <!-- HISRC Retina Images -->
  <script src="/b/js/plugins/hisrc/hisrc.js"></script>
  <script src="/b/js/flex.js"></script>
  <script src="/b/js/main.js?v=20150728"></script>
</body>
</html>