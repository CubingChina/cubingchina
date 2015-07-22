<div class="col-lg-12">
  <div class="text-center">
    <?php echo CHtml::image('http://s.cubingchina.com/animatedcube.gif'); ?>
    <br>
    <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('pay',
<<<EOT
  $.ajax({
    url: '/pay/url',
    data: {
      id: '{$model->id}',
      is_mobile: Number('ontouchstart' in window)
    },
    dataType: 'json',
    success: function(data) {
      location.href = data.data.url;
    }
  });
EOT
);