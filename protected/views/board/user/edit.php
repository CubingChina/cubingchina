<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>用户</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>用户信息</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', [
            'htmlOptions'=>[
              'class'=>'clearfix row',
            ],
            'enableClientValidation'=>true,
          ]); ?>
          <?php echo Html::formGroup(
            $model, 'name_zh', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'name_zh', [
              'label'=>'中文名',
            ]),
            Html::activeTextField($model, 'name_zh'),
            $form->error($model, 'name_zh', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'name', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'name', [
              'label'=>'英文名',
            ]),
            Html::activeTextField($model, 'name'),
            $form->error($model, 'name', ['class'=>'text-danger'])
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'email', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'email', [
              'label'=>'邮箱',
            ]),
            Html::activeTextField($model, 'email'),
            $form->error($model, 'email', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'mobile', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'mobile', [
              'label'=>'手机号码',
            ]),
            Html::activeTextField($model, 'mobile'),
            $form->error($model, 'mobile', ['class'=>'text-danger'])
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'wcaid', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'wcaid', [
              'label'=>'WCA ID',
            ]),
            Html::activeTextField($model, 'wcaid'),
            $form->error($model, 'wcaid', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'country_id', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'country_id', [
              'label'=>'国家',
            ]),
            $form->dropDownList($model, 'country_id', Region::getCountries(), [
              'prompt'=>'',
              'class'=>'form-control',
            ]),
            $form->error($model, 'country_id', ['class'=>'text-danger'])
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'province_id', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'province_id', [
              'label'=>'省份',
            ]),
            $form->dropDownList($model, 'province_id', Region::getProvinces(), [
              'class'=>'form-control',
              'prompt'=>'',
            ]),
            $form->error($model, 'province_id', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'city_id', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'city_id', [
              'label'=>'城市',
            ]),
            $form->dropDownList($model, 'city_id', isset($cities[$model->province_id]) ? $cities[$model->province_id] : [], [
              'prompt'=>'',
              'class'=>'form-control',
            ]),
            $form->error($model, 'city_id', ['class'=>'text-danger'])
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'gender', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'gender', [
              'label'=>'性别',
            ]),
            $form->dropDownList($model, 'gender', $genders, [
              'prompt'=>'',
              'class'=>'form-control',
            ]),
            $form->error($model, 'gender', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'birthday', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'birthday', [
              'label'=>'生日',
            ]),
            Html::activeTextField($model, 'birthday', [
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd',
              'data-min-view'=>'2',
            ]),
            $form->error($model, 'birthday', ['class'=>'text-danger'])
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'identity', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'identity', [
              'label'=>'身份',
            ]),
            $form->dropDownList($model, 'identity', $identities, [
              'class'=>'form-control',
            ]),
            $form->error($model, 'identity', ['class'=>'text-danger'])
          );?>
          <?php echo Html::formGroup(
            $model, 'role', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'role', [
              'label'=>'角色',
            ]),
            $form->dropDownList($model, 'role', $roles, [
              'class'=>'form-control',
            ]),
            $form->error($model, 'role', ['class'=>'text-danger'])
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'passport_type', [
              'class'=>'col-lg-3 col-md-6'
            ],
            $form->labelEx($model, 'passport_type'),
            $form->dropDownList($model, 'passport_type', User::getPassportTypes(), [
              'prompt'=>'',
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_type', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'passport_name', [
              'class'=>'col-lg-3 col-md-6'
            ],
            $form->labelEx($model, 'passport_name'),
            Html::activeTextField($model, 'passport_name', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_name', ['class'=>'text-danger'])
          ); ?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'passport_number', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'passport_number'),
            Html::activeTextField($model, 'passport_number', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_number', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'show_as_delegate', [
              'class'=>'col-lg-3 col-md-6',
            ],
            $form->labelEx($model, 'show_as_delegate', [
              'label'=>'在代表页展示',
            ]),
            Html::activeSwitch($model, 'show_as_delegate'),
            $form->error($model, 'show_as_delegate', ['class'=>'text-danger'])
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'avatar_id', [
              'class'=>'col-lg-12',
            ],
            $form->labelEx($model, 'avatar_id', [
              'label'=>'头像',
            ]),
            $form->radioButtonList($model, 'avatar_id', $model->avatarList, [
              'class'=>'form-control',
              'container'=>'div',
              'separator'=>'',
              'template'=>'<div class="radio user-avatar-option">{beginLabel}{input}{labelTitle}{endLabel}</div>',
            ]),
            $form->error($model, 'avatar_id', ['class'=>'text-danger'])
          );?>
          <div class="col-lg-12">
            <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Submit'); ?></button>
          </div>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerPackage('datetimepicker');
$allCities = json_encode($cities);
Yii::app()->clientScript->registerScript('user',
<<<EOT
  $('.datetime-picker').datetimepicker({
    autoclose: true
  });
  var allCities = {$allCities};
  $(document).on('change', '#User_province_id', function() {
    var city = $('#User_city_id'),
      cities = allCities[$(this).val()] || [];
    city.empty();
    $('<option value="">').appendTo(city);
    $.each(cities, function(id, name) {
      $('<option>').val(id).text(name).appendTo(city);
    });
  });
EOT
);
