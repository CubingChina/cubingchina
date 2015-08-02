<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>FAQ</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>FAQ信息</h4>
          </div>
          <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
          <div class="portlet-body">
            <?php $form = $this->beginWidget('CActiveForm', array(
              'htmlOptions'=>array(
                'class'=>'clearfix row',
              ),
              'enableClientValidation'=>true,
            )); ?>
            <?php echo Html::formGroup(
              $model, 'title_zh', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'title_zh', array(
                'label'=>'中文标题',
              )),
              Html::activeTextField($model, 'title_zh'),
              $form->error($model, 'title_zh', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'title', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'title', array(
                'label'=>'英文标题',
              )),
              Html::activeTextField($model, 'title'),
              $form->error($model, 'title', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'date', array(
                'class'=>'col-lg-6',
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
            <?php echo Html::formGroup(
              $model, 'category_id', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'category_id', array(
                'label'=>'分类',
              )),
              $form->dropDownList($model, 'category_id', FaqCategory::getCategories(), array(
                'class'=>'form-control',
                'prompt'=>'',
              )),
              $form->error($model, 'category_id', array('class'=>'text-danger'))
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'content_zh', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'content_zh', array(
                'label'=>'中文正文',
              )),
              $form->textArea($model, 'content_zh', array(
                'class'=>'editor form-control'
              )),
              $form->error($model, 'content_zh', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'content', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'content', array(
                'label'=>'英文正文',
              )),
              $form->textArea($model, 'content', array(
                'class'=>'editor form-control'
              )),
              $form->error($model, 'content', array('class'=>'text-danger'))
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
$this->widget('Editor');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js');
Yii::app()->clientScript->registerScript('category',
<<<EOT
  $('.datetime-picker').datetimepicker({
    autoclose: true
  });
EOT
);