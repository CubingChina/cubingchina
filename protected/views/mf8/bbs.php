<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style type="text/css">
  body {
    color: #000000;
  }
  p {
    font-size: 12px
  }
  a {
    color: #000000;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
  a.competition {
    color: #0000ff;
  }
  span.red {
    color: #ff0000;
  }
  .bold {
    font-weight: bold;
  }
  </style>
</head>
<body>
<p>
  <a href="http://www.mf8-china.com/" target="_blank">中国魔方俱乐部首页</a>
  <a href="http://weibo.com/mf8china" target="_blank">互动微博</a>
  <?php echo CHtml::link(Yii::t('common', 'Cubing China'), array('/site/index'), array('target'=>'_blank', 'class'=>'bold')); ?>
  <?php if (!empty($upcomingCompetitions)): ?>
    （<span class="red">赛事报名：</span>
    <?php foreach ($upcomingCompetitions as $competition): ?>
    <?php echo CHtml::link($competition->getAttributeValue('name'), $competition->getUrl('detail'), array('target'=>'_blank', 'class'=>'competition')); ?>
    <?php endforeach; ?>
    ）
  <?php endif; ?>
</p>
</body>
</html>
