<?php $this->renderPartial('side', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <?php $this->widget('GridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($competitions, []),
    // 'filter'=>false,
    'enableSorting'=>false,
    'front'=>true,
    'emptyText'=>Yii::t('Competition', 'You don\'t have any certificate.'),
    'columns'=>array(
      array(
        'header'=>Yii::t('Competition', 'Name'),
        'type'=>'raw',
        'value'=>'$data->getMyCertUrl()',
      ),
      array(
        'header'=>Yii::t('Competition', 'Date'),
        'type'=>'raw',
        'value'=>'$data->getDisplayDate()',
      ),
    ),
  )); ?>
</div>