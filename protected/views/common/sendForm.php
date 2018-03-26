<div class="text-center">
  <?php echo CHtml::image('https://i.cubingchina.com/animatedcube.gif'); ?>
  <br>
  <?php echo Yii::t('common', 'You are being redirected, please wait patiently.'); ?>
</div>
<?php echo CHtml::openTag('form', [
  'action'=>$url,
  'method'=>'post',
  'id'=>'form',
]);
foreach ($params as $key=>$value) {
  echo CHtml::hiddenField($key, $value);
}
echo CHtml::closeTag('form');
Yii::app()->clientScript->registerScript('sendForm',
<<<EOT
$('#form').submit();
EOT
);
