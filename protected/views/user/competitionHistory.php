<?php $this->renderPartial('side', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php $this->widget('GridView', array(
    'dataProvider'=>$model->searchUser($this->user->wcaid),
    // 'filter'=>false,
    'enableSorting'=>false,
    'front'=>true,
    'emptyText'=>Yii::t('Competition', 'You have not registered for any competition.'),
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
        'value'=>'$data->country ? Region::getIconName($data->country->name, $data->country->iso2) : $data->countryId',
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