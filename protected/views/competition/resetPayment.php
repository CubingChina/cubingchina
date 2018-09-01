<?php if ($registration->payable): ?>
<div class="row">
  <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
    <div class="panel panel-default">
      <div class="panel-heading">
        <a data-toggle="collapse" href="#reset"><?php echo Yii::t('Registration', 'Reset Payment'); ?></a>
      </div>
      <div class="panel-body collapse" id="reset">
        <p><?php echo Yii::t('Registration', 'When one of the following occurs, you can reset your order here.'); ?></p>
        <ol>
          <li><?php echo Yii::t('Registration', 'Your order has been closed by Alipay due to not paying in time.'); ?></li>
          <li><?php echo Yii::t('Registration', 'You have clicked "Pay" above but want to change events now.'); ?></li>
        </ol>
        <?php $form = $this->beginWidget('ActiveForm', array(
          'id'=>'reset-form',
          'htmlOptions'=>array(
          ),
        )); ?>
        <input type="hidden" name="reset" value="1">
        <?php echo CHtml::tag('button', [
          'id'=>'reset',
          'type'=>'submit',
          'class'=>'btn btn-warning',
        ], Yii::t('common', 'Reset')); ?>
        <?php $this->endWidget(); ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
