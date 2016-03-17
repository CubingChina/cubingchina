<?php echo CHtml::tag('div', array(
  'id'=>'live-container',
  'data-competition-id'=>$competition->id,
  'data-events'=>json_encode($competition->events),
  'data-user'=>json_encode(array(
    'isGuest'=>Yii::app()->user->isGuest,
    'name'=>Yii::app()->user->isGuest ? '' : $this->user->getCompetitionName(),
  )),
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
    <ul class="unstyled">
      <li v-for="message in messages">
        <message :message="message"></message>
      </li>
    </ul>
  </div>
  <div class="input-panel">
    <input v-model="message" @keyup.enter="send" :disabled="$store.state.user.isGuest" placeholder="<?php echo Yii::app()->user->isGuest ? Yii::t('common', 'Please login.') : ''; ?>" />
  </div>
</template>

<template id="message-template">
  <div class="chat-message" :class="{'self-message': message.isSelf}">
    <div class="message-meta">
      {{message.user.name}} {{message.time | formatTime}}
    </div>
    <div class="message-body">
      {{message.content}}
    </div>
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
          <td class="result">{{result.best | formatTime result.event}}</td>
          <td class="record">{{result.regional_single_record}}</td>
          <td class="result">{{result.average | formatTime result.event}}</td>
          <td class="record">{{result.regional_average_record}}</td>
          <td>{{result.region}}</td>
          <td>{{result.detail}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
