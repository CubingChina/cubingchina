<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>申请资料</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>申请资料</h4>
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
          <?php echo $form->errorSummary($model, null, null, array(
            'class'=>'text-danger col-lg-12',
          )); ?>
          <div class="col-sm-12">
            <p class="text-danger">
              <b>友情提示</b>：申请资料可以多次编辑，请注意保存。
            </p>
          </div>
          <?php $this->renderPartial('editorTips'); ?>
          <?php echo Html::formGroup(
            $model, 'organized_competition', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'organized_competition', array(
              'label'=>'主办/参与过的比赛',
            )),
            $form->textArea($model, 'organized_competition', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">列举你主办过的比赛（包括性质、规模等）、参与过工作人员的比赛（何种职位），可上传照片</div>',
            $form->error($model, 'organized_competition', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'schedule', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'schedule', array(
              'label'=>'预估赛程',
            )),
            $form->textArea($model, 'schedule', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">贴上你预先安排的赛程（可上传图片）</div>',
            $form->error($model, 'schedule', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'self_introduction', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'self_introduction', array(
              'label'=>'申请人自我阐述',
            )),
            $form->textArea($model, 'self_introduction', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">对自己组织能力的阐述</div>',
            $form->error($model, 'self_introduction', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'team_introduction', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'team_introduction', array(
              'label'=>'主办团队介绍',
            )),
            $form->textArea($model, 'team_introduction', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">说明团队组成、打乱裁判各方面能力等</div>',
            $form->error($model, 'team_introduction', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'venue_detail', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'venue_detail', array(
              'label'=>'比赛场地',
            )),
            $form->textArea($model, 'venue_detail', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">场地介绍（光线、容量、温度、布局等），附上场地照片，并说明与场地提供方关系</div>',
            $form->error($model, 'venue_detail', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'budget', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'budget', array(
              'label'=>'预算',
            )),
            $form->textArea($model, 'budget', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">列举主要资金来源、预估花销、应急方案</div>',
            $form->error($model, 'budget', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'sponsor', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'sponsor', array(
              'label'=>'支持单位',
            )),
            $form->textArea($model, 'sponsor', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">主要支持单位及支持形式</div>',
            $form->error($model, 'sponsor', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'other', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'other', array(
              'label'=>'其他',
            )),
            $form->textArea($model, 'other', array(
              'class'=>'editor form-control'
            )),
            '<div class="text-danger">其他上面没有提及但需要补充的部分</div>',
            $form->error($model, 'other', array('class'=>'text-danger'))
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
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerScript('competitionApplication',
<<<EOT
  $('[data-toggle="tooltip"]').tooltip();
  $('.datetime-picker').on('mousedown touchstart', function() {
    $(this).datetimepicker({
      autoclose: true
    });
  });
EOT
);
