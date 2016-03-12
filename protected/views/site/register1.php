<?php $this->renderPartial('registerSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Step 1. Have you ever participated in WCA competitions?'); ?>
  </h3>
  <div class="progress progress-striped active">
    <div class="progress-bar progress-bar-theme" style="width: 33%">
      <span class="sr-only"><?php echo Yii::t('common', 'Step One'); ?></span>
    </div>
  </div>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'id'=>'register-form',
    'htmlOptions'=>array(
      //'class'=>'form-login',
      'role'=>'form',
    ),
  )); ?>
    <div class="register-area">
      <div class="col-xs-6">
        <p><?php echo Yii::t('common', 'Please enter your WCA ID below. You can find your WCA ID {here}.', array(
          '{here}'=>CHtml::link(Yii::t('common', 'here'), 'http://cubingchina.com/faq/1.html#faq-5', array('target'=>'_blank')),
        )); ?></p>
        <p>
          <?php echo $form->textField($model, 'wcaid', array(
            'class'=>'form-control',
            'placeholder'=>Yii::t('common', 'Enter your WCA ID'),
          )); ?>
        </p>
        <?php echo $form->error($model, 'wcaid', array('class'=>'text-danger')); ?>
      </div>
      <div class="col-xs-6">
        <p>&nbsp;</p>
        <p><?php echo Yii::t('common', 'You will have a WCA ID after you compete in any WCA competition.'); ?></p>
      </div>
      <div class="clearfix"></div>
      <div class="col-xs-6">
        <p class="text-center">
          <button type="submit" class="btn btn-success btn-lg" id="yes"><?php echo Yii::t('common', 'Yes'); ?></button>
        </p>
      </div>
      <div class="col-xs-6">
        <p class="text-center">
          <button type="submit" class="btn btn-danger btn-lg"><?php echo Yii::t('common', 'No'); ?></button>
        </p>
      </div>
    </div>
  <?php $this->endWidget(); ?>
</div>
<?php
Yii::app()->clientScript->registerScript('register1',
<<<EOT
  $(document).on('click', '#yes', function() {
    var button = $('#RegisterForm_wcaid'),
      wcaid = $.trim(button.val());
    if (wcaid === '' || !/\d{4}[a-z]{4}\d\d/i.test(wcaid)) {
      button.parent().addClass('has-error').prev().addClass('text-danger');
      return false;
    } else {
      button.parent.removeClass('has-error').prev().removeClass('text-danger');
    }
  });
EOT
);