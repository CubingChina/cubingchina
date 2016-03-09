<?php echo CHtml::tag('div', array(
  'id'=>'live-container',
  'data-competition-id'=>$competition->id,
  'data-logged-in'=>!Yii::app()->user->isGuest,
  'data-user-id'=>Yii::app()->user->id,
  'data-user-name'=>Yii::app()->user->name,
  'v-cloak'=>true,
), ''); ?>

<template id="live-container-template">
  <div class="col-lg-12">
    <chat></chat>
    <result></result>
  </div>
</template>

<template id="chat-template">
  <div class="message-container">
    <ul>
      <li v-for="message in messages">{{message.text}}</li>
    </ul>
  </div>
  <div class="input-panel">
    <input v-model="message" @keyup.enter="send" />
  </div>
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
        <tr v-for="result in results" :class="{danger: result.isNew}">
          <td>{{result.pos}}</td>
          <td>{{{result.user}}}</td>
          <td class="result">{{result.best}}</td>
          <td class="record">{{result.regional_single_record}}</td>
          <td class="result">{{result.average}}</td>
          <td class="record">{{result.regional_average_record}}</td>
          <td>{{result.region}}</td>
          <td>{{result.detail}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>