<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>FAQ分类</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>FAQ分类信息</h4>
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
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'name_zh', array(
                'label'=>'中文名字',
              )),
              Html::activeTextField($model, 'name_zh'),
              $form->error($model, 'name_zh', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'name', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'name', array(
                'label'=>'英文名字',
              )),
              Html::activeTextField($model, 'name'),
              $form->error($model, 'name', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'date', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'date', array(
                'label'=>'时间',
              )),
              Html::activeTextField($model, 'date', array(
                'class'=>'datetime-picker',
                'data-date-format'=>'yyyy-mm-dd hh:ii:ss',
              )),
              $form->error($model, 'date', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
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
$this->widget('Editor');
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerScript('category',
<<<EOT
  $('.datetime-picker').datetimepicker({
    autoclose: true
  });
EOT
);