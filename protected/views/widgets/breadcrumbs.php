<div class="breadcrumbs pull-right hidden-xs">
  <ul class="breadcrumbs-list">
    <li class="breadcrumbs-label"><?php echo Yii::t('common', 'You are here:'); ?></li>
    <li><?php echo CHtml::link(Yii::t('common', 'Home'), array('/site/index')); ?><i class="fa fa-angle-right"></i></li>
    <?php
    $i = 0;
    foreach ($this->breadcrumbs as $label=>$url) {
      $i++;
      if(is_string($label) || is_array($url)) {
        $text = Yii::t('common', $label);
        $crumb = CHtml::link($text, $url);
      } else {
        $crumb = CHtml::tag('span', array(), Yii::t('common', $url));
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