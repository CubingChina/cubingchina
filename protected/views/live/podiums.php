<div class="col-lg-12">
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider(call_user_func_array('array_merge', $podiums), [
      'pagination'=>false,
      'sort'=>false,
    ]),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'rowCssClassExpression'=>'$data->getSortClass()',
    'groupKey'=>'event',
    'groupHeader'=>'Events::getFullEventNameWithIcon($data->event)',
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
        'value'=>'Results::formatTime($data->best, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-best'],
      ],
      [
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'Results::formatTime($data->average, $data->event)',
        'headerHtmlOptions'=>['class'=>'result'],
        'htmlOptions'=>['class'=>'result result-average'],
      ],
      [
        'name'=>Yii::t('common', 'Region'),
        'value'=>'Region::getIconName($data->user->country->name, $data->user->country->wcaCountry->iso2)',
        'type'=>'raw',
        'htmlOptions'=>['class'=>'region'],
      ],
      [
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->detail',
      ],
    ],
  )); ?>
</div>
