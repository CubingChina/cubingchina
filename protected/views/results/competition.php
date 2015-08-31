<div class="col-lg-12">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/results/competition'),
  )); ?>
    <div class="form-group">
      <label for="year"><?php echo Yii::t('common', 'Year'); ?></label>
      <?php echo CHtml::dropDownList('year', $model->year, Competitions::getYears(), array(
        'class'=>'form-control',
        'prompt'=>Yii::t('common', 'All'),
      )); ?>
    </div>
    <div class="form-group">
      <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
      <?php echo CHtml::dropDownList('region', $model->region, Region::getWCARegions(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="form-group">
      <label for="event"><?php echo Yii::t('common', 'Event'); ?></label>
      <?php echo CHtml::dropDownList('event', $model->event, Events::getNormalTranslatedEvents(), array(
        'class'=>'form-control',
        'prompt'=>Yii::t('common', 'All'),
      )); ?>
    </div>
    <div class="form-group">
      <label for="name"><?php echo Yii::t('Results', 'Name, City or Venue'); ?></label>
      <?php echo CHtml::textField('name', $model->name, array(
        'class'=>'form-control',
      )); ?>
    </div>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <?php
  $this->widget('GridView', array(
    'dataProvider'=>$model->search(),
    'template'=>'{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'rowCssClassExpression'=>'$data->isInProgress() ? "success" : ($data->isEnded() ? "active" : "info")',
    'columns'=>array(
      array(
        'name'=>'date',
        'header'=>Yii::t('Competition', 'Date'),
        'type'=>'raw',
        'value'=>'$data->getDate()',
      ),
      array(
        'name'=>'name',
        'header'=>Yii::t('Competition', 'Name'),
        'type'=>'raw',
        'value'=>'$data->getCompetitionLink()',
      ),
      array(
        'name'=>'countryId',
        'header'=>Yii::t('common', 'Region'),
        'type'=>'raw',
        'value'=>'$data->country ? Region::getIconName($data->country->name, $data->country->iso2) : $data->cityName',
        'htmlOptions'=>array('class'=>'region'),
      ),
      array(
        'name'=>'cityName',
        'header'=>Yii::t('common', 'City'),
        'type'=>'raw',
        'value'=>'$data->cityName',
      ),
    ),
  )); ?>
</div>