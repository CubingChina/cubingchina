<?php $this->setPageTitle(array('Please update your browser')); ?>
<div class="col-lg-12">
  <h1><?php echo Yii::t('common', 'Please update your browser'); ?></h1>
  <?php if (Yii::app()->language == 'zh_cn'): ?>
  <p>使用山寨浏览器的童鞋请从兼容模式切换到极速模式。</p>
  <?php endif; ?>
  <p>
    <b><?php echo Yii::t('common', 'Recommended Browsers:'); ?></b>&nbsp;
    <a href="https://www.mozilla.org/firefox/" target="_blank">Firefox</a>&nbsp;
    <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a>&nbsp;
    <a href="http://www.apple.com/safari/download" target="_blank">Safari</a>
  </p>
</div>