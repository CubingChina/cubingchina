
<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->name_zh; ?> - 赛程</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>赛程安排</h4>
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
            $model, 'schedules', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'schedules', array(
              'label'=>'赛程',
            )),
            '<div class="text-danger">时间会自动排序，留空时间即可删除某项，无分组请留空</div>',
            $this->widget('SchedulesForm', array(
              'model'=>$model,
              'name'=>'schedules',
            ), true),
            $form->error($model, 'schedules', array('class'=>'text-danger'))
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
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  $('.datetime-picker').on('mousedown touchstart', function() {
    $(this).datetimepicker({
      autoclose: true
    });
  });
EOT
);
