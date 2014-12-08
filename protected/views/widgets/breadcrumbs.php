<div class="breadcrumbs pull-right hidden-xs">
  <ul class="breadcrumbs-list">
    <li class="breadcrumbs-label"><?php echo Yii::t('common', 'You are here:'); ?></li>
    <li><a href="/"><?php echo Yii::t('common', 'Home'); ?></a><i class="fa fa-angle-right"></i></li>
    <?php
    $i = 0;
    foreach ($this->breadcrumbs as $label=>$url) {
      $i++;
      if(is_string($label) || is_array($url)) {
        $text = Yii::t('common', $label);
        $crumb = CHtml::link($text, $url);
      } else {
        $crumb = Yii::t('common', $url);
      }
      $options = array();
      if ($i !== count($this->breadcrumbs)) {
        $crumb .= Html::fontAwesome('angle-right');
      } else {
        $options['class'] = 'current';
      }
      echo CHtml::tag('li', $options, $crumb);
    }?>
  </ul>
</div><!--//breadcrumbs-->