<?php
$allCities = Region::getAllCities();
$this->renderPartial('side', $_data_);
?>
<div class="content-wrapper col-md-10 col-sm-9">
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Edit profile.'); ?>
  </h3>
  <?php $form = $this->beginWidget('ActiveForm', array(
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
  <?php if ($user->passport_type == User::NO): ?>
  <?php echo Html::formGroup(
    $model, 'passport_type', array(),
    $form->labelEx($model, 'passport_type'),
    $form->dropDownList($model, 'passport_type', User::getPassportTypes(), array(
      'prompt'=>'',
      'class'=>'form-control',
    )),
    $form->error($model, 'passport_type', array('class'=>'text-danger'))
  ); ?>
  <?php echo Html::formGroup(
    $model, 'passport_name', array(
      'class'=>'hide',
    ),
    $form->labelEx($model, 'passport_name'),
    Html::activeTextField($model, 'passport_name', array(
      'class'=>'form-control',
    )),
    $form->error($model, 'passport_name', array('class'=>'text-danger'))
  ); ?>
  <?php echo Html::formGroup(
    $model, 'passport_number', array(),
    $form->labelEx($model, 'passport_number'),
    Yii::app()->language == 'zh_cn' ? '<div class="help-text">如果您提供的身份证件为个人身份证，请注意身份证上的<b class="text-danger">出生日期必须与您在粗饼网注册的信息一致</b>，否则会提示输入错误。需要修改生日信息，请联系admin@cubingchina.com。</div>' : '',
    Html::activeTextField($model, 'passport_number', array(
      'class'=>'form-control',
    )),
    $form->error($model, 'passport_number', array('class'=>'text-danger'))
  ); ?>
  <?php echo Html::formGroup(
    $model, 'repeatPassportNumber', array(),
    $form->labelEx($model, 'repeatPassportNumber'),
    Html::activeTextField($model, 'repeatPassportNumber', array(
      'class'=>'form-control',
    )),
    $form->error($model, 'repeatPassportNumber', array('class'=>'text-danger'))
  ); ?>
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
    '{email}'=>CHtml::mailto(Html::fontAwesome('envelope', 'a') . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
  )); ?></p>
  <button type="submit" class="btn btn-theme btn-lg"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
<?php
$allCities = json_encode($allCities);
Yii::app()->clientScript->registerScript('edit',
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
    }).on('change', '#EditProfileForm_passport_type', function() {
      changePassportType(true);
    }).on('contextmenu', '#EditProfileForm_passport_number, #EditProfileForm_repeatPassportNumber', function(e) {
      e.preventDefault();
      return false;
    }).on('keydown', '#EditProfileForm_passport_number, #EditProfileForm_repeatPassportNumber', function(e) {
      if (e.which == 86 && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        return false;
      }
    });
  $('label[for="EditProfileForm_passport_name"]').append('<span class="required">*</span>');
  changePassportType();
  function changePassportType(focus) {
    var type = $('#EditProfileForm_passport_type').val();
    if (type == 3) {
      $('#EditProfileForm_passport_name').parent().removeClass('hide');
      if (focus) {
        $('#EditProfileForm_passport_name').focus();
      }
    } else {
      $('#EditProfileForm_passport_name').parent().addClass('hide');
    }
  }
  if ($('label[for="EditProfileForm_province_id"]').length > 0) {
    $('label[for="EditProfileForm_mobile"]').append('<span class="required">*</span>');
    $('label[for="EditProfileForm_province_id"]').append('<span class="required">*</span>');
    $('label[for="EditProfileForm_city_id"]').append('<span class="required">*</span>');
  }
EOT
);
