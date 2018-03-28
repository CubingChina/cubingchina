<div class="row">
  <div class="col-lg-12">
    <div class="page-name">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>应用</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
          <div class="portlet-title">
              <h4>应用信息</h4>
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
                'class'=>'col-lg-6',
              ],
              $form->labelEx($model, 'name_zh', [
              ]),
              Html::activeTextField($model, 'name_zh'),
              $form->error($model, 'name_zh', ['class'=>'text-danger'])
            );?>
            <?php echo Html::formGroup(
              $model, 'name', [
                'class'=>'col-lg-6',
              ],
              $form->labelEx($model, 'name', [
              ]),
              Html::activeTextField($model, 'name'),
              $form->error($model, 'name', ['class'=>'text-danger'])
            );?>
            <div class="clearfix"></div>
            <?php echo Html::formGroup(
              $model, 'scopes', [
                'class'=>'col-lg-12',
              ],
              $form->labelEx($model, 'scopes', [
              ]),
              Html::activeTextField($model, 'scopes'),
              $form->error($model, 'scopes', ['class'=>'text-danger'])
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
Yii::app()->clientScript->registerScript('application',
<<<EOT
EOT
);
