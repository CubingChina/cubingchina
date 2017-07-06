<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>退赛确认</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>退赛确认</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', [
            'htmlOptions'=>[
              'class'=>'form-horizontal'
            ],
          ]); ?>
          <?php echo Html::formGroup(
            $model, 'competition', array(),
            $form->labelEx($model, 'competition_id', [
              'class'=>'col-sm-2 control-label',
              'required'=>false,
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], $model->competition->name_zh))
          );?>
          <?php echo Html::formGroup(
            $model, 'user', array(),
            $form->labelEx($model, 'user_id', [
              'class'=>'col-sm-2 control-label',
              'label'=>'选手',
              'required'=>false,
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], $model->user->getCompetitionName()))
          );?>
          <?php echo Html::formGroup(
            $model, 'number', array(),
            $form->labelEx($model, 'number', [
              'class'=>'col-sm-2 control-label',
              'label'=>'当前编号',
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], $model->getUserNumber()))
          );?>
          <?php echo Html::formGroup(
            $model, 'events', array(),
            $form->labelEx($model, 'events', [
              'class'=>'col-sm-2 control-label',
              'required'=>false,
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], $model->getRegistrationEvents()))
          );?>
          <?php echo Html::formGroup(
            $model, 'totalFee', array(),
            $form->labelEx($model, 'totalFee', [
              'class'=>'col-sm-2 control-label',
              'label'=>'报名费',
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], $model->getTotalFee()))
          );?>
          <?php echo Html::formGroup(
            $model, 'date', array(),
            $form->labelEx($model, 'date', [
              'class'=>'col-sm-2 control-label',
              'required'=>false,
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], date('Y-m-d H:i:s', $model->date)))
          );?>
          <?php echo Html::formGroup(
            $model, 'accept_time', array(),
            $form->labelEx($model, 'accept_time', [
              'class'=>'col-sm-2 control-label',
              'required'=>false,
            ]),
            CHtml::tag('div', ['class'=>'col-sm-10'], CHtml::tag('p', ['class'=>'form-control-static'], date('Y-m-d H:i:s', $model->accept_time)))
          );?>
          <div class="form-group">
            <div class="col-sm-10 col-sm-push-2">
              <button type="submit" name="cancel" class="btn btn-theme">确认退赛</button>
            </div>
          </div>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
