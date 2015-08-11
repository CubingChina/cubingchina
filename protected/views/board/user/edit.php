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
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'clearfix row',
            ),
            'enableClientValidation'=>true,
          )); ?>
          <?php echo Html::formGroup(
            $model, 'name_zh', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'name_zh', array(
              'label'=>'中文名',
            )),
            Html::activeTextField($model, 'name_zh'),
            $form->error($model, 'name_zh', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'name', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'name', array(
              'label'=>'英文名',
            )),
            Html::activeTextField($model, 'name'),
            $form->error($model, 'name', array('class'=>'text-danger'))
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'email', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'email', array(
              'label'=>'邮箱',
            )),
            Html::activeTextField($model, 'email'),
            $form->error($model, 'email', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'mobile', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'mobile', array(
              'label'=>'手机号码',
            )),
            Html::activeTextField($model, 'mobile'),
            $form->error($model, 'mobile', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'wcaid', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'wcaid', array(
              'label'=>'WCA ID',
            )),
            Html::activeTextField($model, 'wcaid'),
            $form->error($model, 'wcaid', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'country_id', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'country_id', array(
              'label'=>'国家',
            )),
            $form->dropDownList($model, 'country_id', Region::getCountries(), array(
              'prompt'=>'',
              'class'=>'form-control',
            )),
            $form->error($model, 'country_id', array('class'=>'text-danger'))
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'province_id', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'province_id', array(
              'label'=>'省份',
            )),
            $form->dropDownList($model, 'province_id', Region::getProvinces(), array(
              'class'=>'form-control',
              'prompt'=>'',
            )),
            $form->error($model, 'province_id', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'city_id', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'city_id', array(
              'label'=>'城市',
            )),
            $form->dropDownList($model, 'city_id', isset($cities[$model->province_id]) ? $cities[$model->province_id] : array(), array(
              'prompt'=>'',
              'class'=>'form-control',
            )),
            $form->error($model, 'city_id', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'gender', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'gender', array(
              'label'=>'性别',
            )),
            $form->dropDownList($model, 'gender', $genders, array(
              'prompt'=>'',
              'class'=>'form-control',
            )),
            $form->error($model, 'gender', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'birthday', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'birthday', array(
              'label'=>'生日',
            )),
            Html::activeTextField($model, 'birthday', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd',
              'data-min-view'=>'2',
            )),
            $form->error($model, 'birthday', array('class'=>'text-danger'))
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'identity', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'identity', array(
              'label'=>'身份',
            )),
            $form->dropDownList($model, 'identity', $identities, array(
              'class'=>'form-control',
            )),
            $form->error($model, 'identity', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'role', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'role', array(
              'label'=>'角色',
            )),
            $form->dropDownList($model, 'role', $roles, array(
              'class'=>'form-control',
            )),
            $form->error($model, 'role', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'avatar_id', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'avatar_id', array(
              'label'=>'头像',
            )),
            $form->radioButtonList($model, 'avatar_id', $model->avatarList, array(
              'class'=>'form-control',
              'container'=>'div',
              'separator'=>'',
              'template'=>'<div class="radio user-avatar-option">{beginLabel}{input}{labelTitle}{endLabel}</div>',
            )),
            $form->error($model, 'avatar_id', array('class'=>'text-danger'))
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
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js');
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