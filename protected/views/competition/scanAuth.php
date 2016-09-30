<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
    ),
  )); ?>
    <div class="form-group">
      <div class="help-text"><?php echo Yii::t('common', 'Please enter authentication code.'); ?></div>
      <?php echo CHtml::textField('scan_code', '', [
        'class'=>'form-control',
      ]); ?>
    </div>
    <button type="submit" class="btn btn-theme" id="submit-button"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
