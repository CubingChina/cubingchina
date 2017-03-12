<div class="col-lg-12">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/competition/index'),
  )); ?>
  <?php echo Html::formGroup(
    $model, 'year', array(),
    $form->labelEx($model, 'year'),
    CHtml::dropDownList('year', $model->year, Competition::getYears(), array(
      'class'=>'form-control',
      'prompt'=>Yii::t('common', 'All'),
    ))
  );?>
  <?php echo Html::formGroup(
    $model, 'type', array(),
    $form->labelEx($model, 'type'),
    CHtml::dropDownList('type', $model->type, Competition::getTypes(), array(
      'class'=>'form-control',
      'prompt'=>Yii::t('common', 'All'),
    ))
  );?>
  <?php echo Html::formGroup(
    $model, 'province', array(),
    $form->labelEx($model, 'province'),
    CHtml::dropDownList('province', $model->province, Region::getProvinces(false), array(
      'class'=>'form-control',
      'prompt'=>Yii::t('common', 'All'),
    ))
  );?>
  <?php echo Html::formGroup(
    $model, 'event', array(),
    $form->labelEx($model, 'event'),
    CHtml::dropDownList('event', $model->event, Events::getNormalTranslatedEvents(), array(
      'class'=>'form-control',
      'prompt'=>Yii::t('common', 'All'),
    ))
  );?>
  <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <?php $this->widget('GridView', array(
    'dataProvider'=>$model->search(),
    // 'filter'=>false,
    'template'=>'{summary}{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'emptyText'=>Yii::t('Competition', 'No competitions now.'),
    'rowCssClassExpression'=>'$data->isInProgress() ? "success" : ($data->isEnded() ? "active" : ($data->isRegistrationStarted() ? ($data->canRegister() ? "danger" : "info") : "warning"))',
    'columns'=>array(
      array(
        'name'=>'date',
        'type'=>'raw',
        'value'=>'$data->getDisplayDate()',
      ),
      array(
        'name'=>'name',
        'type'=>'raw',
        'value'=>'$data->getCompetitionLink() . $data->getCountdown("small", true)',
      ),
      array(
        'name'=>'province_id',
        'type'=>'raw',
        'value'=>'$data->getLocationInfo("province")',
      ),
      array(
        'name'=>'city_id',
        'type'=>'raw',
        'value'=>'$data->getLocationInfo("city")',
      ),
      array(
        'name'=>'venue',
        'type'=>'raw',
        'value'=>'$data->getLocationInfo("venue")',
      ),
    ),
  )); ?>
</div>
