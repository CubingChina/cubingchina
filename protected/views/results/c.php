<div class="col-lg-12 results-competition">
  <div class="panel panel-info competition-detail">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Competition', 'Date'); ?>:</span>
          <span class="info-value"><?php echo $competition->date; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Competition', 'Location'); ?>:</span>
          <span class="info-value"><?php echo $competition->location; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Competition', 'Links'); ?>:</span>
          <span class="info-value"><?php echo $competition->links; ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php if (count($winners) > 0): ?>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/results/c', 'id'=>$competition->id),
  )); ?>
    <?php foreach (Competitions::getResultsTypes() as $_type=>$name): ?>
    <?php echo CHtml::tag('button', array(
      'type'=>'submit',
      'name'=>'type',
      'value'=>$_type,
      'class'=>'btn btn-' . ($type == $_type ? 'warning' : 'theme'),
    ), $name); ?>
    <?php endforeach; ?>
  <?php $this->endWidget(); ?>
  <?php $this->renderPartial('c/' . $type, array(
    'results'=>$$type,
  )); ?>
  <?php endif; ?>
</div>