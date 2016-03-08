<div id="live-container"></div>
<?php echo CHtml::openTag('template', array(
  'id'=>'live-container-template',
  'data-competition-id'=>$competition->id,
  'data-logged-in'=>!Yii::app()->user->isGuest,
  'data-user-id'=>Yii::app()->user->id,
  'data-user-name'=>Yii::app()->user->name,
  'v-cloak'=>true,
)); ?>
  <div class="col-lg-12">
    <chat></chat>
    <result></result>
  </div>
</template>

<template id="chat-template">
  <textarea></textarea>
</template>

<template id="result-template">
  <div class="table-responsive">
    <table class="table table-bordered table-condensed table-hover table-boxed">
      <thead>
        <?php $columns = array(
          array(
            'name'=>Yii::t('Results', 'Place'),
            'value'=>'$data->pos',
            'htmlOptions'=>array('class'=>'place'),
          ),
          array(
            'name'=>Yii::t('Results', 'Person'),
            'value'=>'Persons::getLinkByNameNId($data->personName, $data->personId)',
          ),
          array(
            'name'=>Yii::t('common', 'Best'),
            'value'=>'$data->getTime("best")',
            'htmlOptions'=>array('class'=>'result'),
          ),
          array(
            'name'=>'',
            'value'=>'$data->regionalSingleRecord',
            'htmlOptions'=>array('class'=>'record'),
          ),
          array(
            'name'=>Yii::t('common', 'Average'),
            'value'=>'$data->getTime("average")',
            'htmlOptions'=>array('class'=>'result'),
          ),
          array(
            'name'=>'',
            'value'=>'$data->regionalAverageRecord',
            'htmlOptions'=>array('class'=>'record'),
          ),
          array(
            'name'=>Yii::t('common', 'Region'),
            'value'=>'Region::getIconName($data->person->country->name, $data->person->country->iso2)',
            'htmlOptions'=>array('class'=>'region'),
          ),
          array(
            'name'=>Yii::t('common', 'Detail'),
            'value'=>'$data->detail',
          ),
        ); ?>
        <?php foreach ($columns as $column): ?>
        <?php echo CHtml::tag('th', isset($column['htmlOptions']) ? $column['htmlOptions'] : array(), $column['name']); ?>
        <?php endforeach; ?>
      </thead>
      <tbody>
        <tr v-for="result in results">
          <td>{{result.pos}}</td>
          <td>{{{result.user}}}</td>
          <td>{{result.best}}</td>
          <td>{{result.regional_single_record}}</td>
          <td>{{result.average}}</td>
          <td>{{result.regional_average_record}}</td>
          <td>{{result.region}}</td>
          <td>{{result.detail}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>