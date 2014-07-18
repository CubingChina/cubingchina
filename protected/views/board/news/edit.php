<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>新闻</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>新闻信息</h4>
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
                'label'=>'日期',
              )),
              Html::activeTextField($model, 'date', array(
                'class'=>'date-picker',
                'data-date-format'=>'yyyy-mm-dd',
              )),
              $form->error($model, 'date', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'time', array(
                'class'=>'col-lg-6',
              ),
              $form->labelEx($model, 'time', array(
                'label'=>'时间',
              )),
              Html::activeTextField($model, 'time', array(
                'class'=>'time-picker',
              )),
              $form->error($model, 'time', array('class'=>'text-danger'))
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
                'class'=>'summernote form-control'
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
                'class'=>'summernote form-control'
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
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-datepicker/datepicker3.css');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/bootstrap-timepicker/bootstrap-timepicker.min.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-datepicker/bootstrap-datepicker.js');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/bootstrap-timepicker/bootstrap-timepicker.min.js');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/summernote/summernote.css');
Yii::app()->clientScript->registerCssFile('/b/css/plugins/summernote/summernote-bs3.css');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/summernote/summernote.min.js');
Yii::app()->clientScript->registerScriptFile('/b/js/plugins/summernote/summernote-zh-CN.js');
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  $('.date-picker').datepicker({
    autoclose: true
  });
  $('.time-picker').timepicker({
    showMeridian: false,
    defaultTime: null,
    showSeconds: true
  });
  $('.summernote').summernote({
    height: 300,
    lang: 'zh-CN',
    toolbar: [
      ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul', 'ol']],
      ['picture', ['link', 'picture', 'video', 'table']],
      ['code', ['fullscreen', 'codeview', 'undo', 'redo']]
    ]
  });
EOT
);