<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->name_zh; ?> - 项目</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>比赛项目</h4>
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
            $model, 'events',array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'events', array(
              'label'=>'项目',
            )),
            '<div class="well">
              不开设的项目，请在轮次处<b>填写0或者留空</b>
              <br>
              <br>
              各项目均可设置最多三个阶段的报名费，通常只需要填写第一阶段，当且仅当上述分阶段报名费时间设置时，此处的项目费用会生效，并且会覆盖比赛设置的倍率。
              <br>
              意即如设置了分阶段报名费，那么该项目在第二或第三阶段的报名费下面设置的<b>优先权高于</b>比赛设置的倍率。如果项目单独设置第二或第三阶段费用，则按此处下面设置，否则按比赛设置的倍率执行。
              <br>
              举例说明，设置第二阶段倍率为1.5，设置三阶第一阶段报名费为10，第二阶段为12，设置四阶第一阶段报名费为20，第二阶段留空，那么到达第二阶段时，三阶报名费为12，四阶为20×1.5=30。
              <br>
              <span class="text-danger">注意：第一阶段不写或写0表示报名费为0，第二或第三阶段表示不单独设置此项。</span>
            ',
            $model->has_qualifying_time ? '<br><br>资格线的单位速拧项目为<b>秒</b>，多盲为<b>得分</b>，最少步为<b>步数</b>；0表示不设限制，9999表示需要有官方成绩；若单次平均皆设置，则<b>只需达到一个</b>即可。' : '',
            '</div>',
            '<div class="row"><div class="col-lg-12"><strong>常规项目</strong></div></div>',
            $this->widget('EventsForm', array(
              'model'=>$model,
              'name'=>'associatedEvents',
              'events'=>Events::getNormalEvents(),
              'type'=>'range',
              'numberOptions'=>array(
                'min'=>0,
                'max'=>4,
              ),
              'feeOptions'=>array(
                'min'=>0,
              ),
            ), true),
            '<div class="row"><div class="col-lg-12"><strong>其它项目</strong></div></div>',
            $this->widget('EventsForm', array(
              'model'=>$model,
              'name'=>'associatedEvents',
              'events'=>Events::getOtherEvents(),
              'type'=>'range',
              'numberOptions'=>array(
                'min'=>0,
                'max'=>4,
              ),
              'feeOptions'=>array(
                'min'=>0,
              ),
            ), true),
            $form->error($model, 'events', array('class'=>'text-danger'))
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
