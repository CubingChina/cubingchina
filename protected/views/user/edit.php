<?php
$allCities = Region::getAllCities();
$this->renderPartial('side', $_data_);
?>
<div class="content-wrapper col-md-10 col-sm-9">
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Edit profile.'); ?>
  </h3>
  <?php $form = $this->beginWidget('CActiveForm', array(
    // 'id'=>'register-form',
    'htmlOptions'=>array(
      //'class'=>'form-login',
      'role'=>'form',
    ),
  )); ?>
  <?php if ($user->wcaid == ''): ?>
  <?php echo Html::formGroup(
    $model, 'wcaid', array(),
    $form->labelEx($model, 'wcaid'),
    Html::activeTextField($model, 'wcaid'),
    $form->error($model, 'wcaid', array('class'=>'text-danger'))
  );?>
  <?php endif; ?>
  <?php if ($user->country_id == 1): ?>
  <?php echo Html::formGroup(
    $model, 'province_id', array(
      'id'=>'province',
    ),
    $form->labelEx($model, 'province_id'),
    $form->dropDownList($model, 'province_id', Region::getProvinces(), array(
      'class'=>'form-control',
      'prompt'=>'',
    )),
    $form->error($model, 'province_id', array('class'=>'text-danger'))
  );?>
  <?php echo Html::formGroup(
    $model, 'city_id', array(
      'id'=>'city',
    ),
    $form->labelEx($model, 'city_id'),
    $form->dropDownList($model, 'city_id', isset($allCities[$model->province_id]) ? $allCities[$model->province_id] : array(), array(
      'class'=>'form-control',
    )),
    $form->error($model, 'city_id', array('class'=>'text-danger'))
  );?>
  <?php endif; ?>
  <?php echo Html::formGroup(
    $model, 'mobile', array(),
    $form->labelEx($model, 'mobile'),
    Html::activeTextField($model, 'mobile'),
    $form->error($model, 'mobile', array('class'=>'text-danger'))
  );?>
  <p class="text-info"><?php echo Yii::t('common', 'Please contact the adminstrator via {email} for changing the other personal information.', array(
    '{email}'=>CHtml::mailto('<i class="fa fa-envelope"></i> ' . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
  )); ?></p>
  <button type="submit" class="btn btn-theme btn-lg"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
<?php
Yii::app()->clientScript->registerCssFile('/f/plugins/bootstrap-datepicker/css/datepicker.css');
Yii::app()->clientScript->registerScriptFile('/f/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js');
Yii::app()->clientScript->registerScriptFile('/f/js/pinyin.min.js');
$allCities = json_encode($allCities);
Yii::app()->clientScript->registerScript('register2',
<<<EOT
  var allCities = {$allCities};
  $(document)
    .on('change', '#EditProfileForm_province_id', function() {
      var city = $('#EditProfileForm_city_id'),
        cities = allCities[$(this).val()] || [];
      city.empty();
      $.each(cities, function(id, name) {
        $('<option>').val(id).text(name).appendTo(city);
      });
    });
  if ($('label[for="EditProfileForm_province_id"]').length > 0) {
    $('label[for="EditProfileForm_mobile"]').append('<span class="required">*</span>');
    $('label[for="EditProfileForm_province_id"]').append('<span class="required">*</span>');
    $('label[for="EditProfileForm_city_id"]').append('<span class="required">*</span>');
  }
EOT
);