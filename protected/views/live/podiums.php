<?php if (!empty($greaterChinaPodiums)): ?>
<div class="col-lg-6">
  <h3><?php echo Yii::t('common', 'Greater China'); ?></h3>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider(call_user_func_array('array_merge', $greaterChinaPodiums ? array_values($greaterChinaPodiums) : [[]]), [
      'pagination'=>false,
      'sort'=>false,
    ]),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'rowCssClassExpression'=>'$data->getSortClass()',
    'groupKey'=>'event',
    'groupHeader'=>'Events::getFullEventNameWithIcon($data->event) . $data->subEventTitle',
    'columns'=>[
      [
        'name'=>Yii::t('Results', 'Place'),
        'type'=>'raw',
        'value'=>'$data->pos',
        'headerHtmlOptions'=>['class'=>'place'],
      ],
      [
        'name'=>Yii::t('Results', 'Person'),
        'type'=>'raw',
        'value'=>'$data->user->getWcaLink()',
      ],
      [
        'name'=>Yii::t('common', 'Best'),
        'type'=>'raw',
        'value'=>'$data->getRecord("single") . Results::formatTime($data->best, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-best'],
      ],
      [
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->getRecord("average") . Results::formatTime($data->average, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-average'],
      ],
      [
        'name'=>Yii::t('common', 'Region'),
        'value'=>'Region::getIconName($data->user->country->name, $data->user->country->wcaCountry->iso2)',
        'type'=>'raw',
        'htmlOptions'=>['class'=>'region'],
      ],
    ],
  )); ?>
</div>
<?php endif; ?>
<div class="col-lg-<?php echo empty($greaterChinaPodiums) ? 12 : 6; ?>">
  <?php if (!empty($greaterChinaPodiums)): ?>
  <h3><?php echo Yii::t('common', 'Official'); ?></h3>
  <?php endif; ?>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider(call_user_func_array('array_merge', $podiums ? array_values($podiums) : [[]]), [
      'pagination'=>false,
      'sort'=>false,
    ]),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'rowCssClassExpression'=>'$data->getSortClass()',
    'groupKey'=>'event',
    'groupHeader'=>'Events::getFullEventNameWithIcon($data->event) . $data->subEventTitle',
    'columns'=>[
      [
        'name'=>Yii::t('Results', 'Place'),
        'type'=>'raw',
        'value'=>'$data->pos',
        'headerHtmlOptions'=>['class'=>'place'],
      ],
      [
        'name'=>Yii::t('Results', 'Person'),
        'type'=>'raw',
        'value'=>'$data->user->getWcaLink()',
      ],
      [
        'name'=>Yii::t('common', 'Best'),
        'type'=>'raw',
        'value'=>'$data->getRecord("single") . Results::formatTime($data->best, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-best'],
      ],
      [
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->getRecord("average") . Results::formatTime($data->average, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-average'],
      ],
      [
        'name'=>Yii::t('common', 'Region'),
        'value'=>'Region::getIconName($data->user->country->name, $data->user->country->wcaCountry->iso2)',
        'type'=>'raw',
        'htmlOptions'=>['class'=>'region'],
      ],
    ],
  )); ?>
</div>
