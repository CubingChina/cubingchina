<?php $events = $competition->getEventsRounds(); ?>
<?php $params = $competition->getLastActiveEventRound($events); ?>
<?php echo CHtml::tag('div', array(
  'id'=>'live-container',
  'data-competition-id'=>$competition->id,
  'data-events'=>json_encode($events),
  'data-params'=>json_encode($params),
  'data-user'=>json_encode(array(
    'isGuest'=>Yii::app()->user->isGuest,
    'isOrganizer'=>!Yii::app()->user->isGuest && $this->user->isOrganizer() && isset($competition->organizers[$this->user->id]),
    'isDelegate'=>!Yii::app()->user->isGuest && $this->user->isDelegate() && isset($competition->delegates[$this->user->id]),
    'isAdmin'=>Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR),
    'name'=>Yii::app()->user->isGuest ? '' : $this->user->getCompetitionName(),
  )),
  'v-cloak'=>true,
), ''); ?>

<template id="live-container-template">
  <div class="col-lg-12">
    <div class="options-area clearfix">
      <div class="pull-right">
        <button class="btn btn-md btn-warning no-mr" @click="showOptions">
          <i class="fa fa-gear"></i>
        </button>
      </div>
      <div tabindex="-1" id="options-modal" class="modal fade">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <div class="checkbox" v-if="hasPermission">
                <label>
                  <input type="checkbox" v-model="options.enableEntry"> <?php echo Yii::t('live', 'Enable Data Entry'); ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" v-model="options.showMessage"> <?php echo Yii::t('live', 'Show Message on Chat'); ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" v-model="options.alertResult"> <?php echo Yii::t('live', 'Show Result on Chat'); ?>
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" v-model="options.alertRecord"> <?php echo Yii::t('live', 'Show Record on Chat'); ?>
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button data-dismiss="modal" class="btn btn-default" type="button"><?php echo Yii::t('common', 'Close'); ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <chat></chat>
    <result></result>
  </div>
</template>

<template id="chat-template">
  <div class="panel panel-info">
    <div class="panel-heading">
    </div>
    <div class="panel-body">
      <div class="message-container">
        <ul class="unstyled">
          <li v-for="message in messages">
            <message :message="message"></message>
          </li>
        </ul>
      </div>
      <div class="chat-input-panel">
        <div class="col-sm-10 col-lg-11">
          <input v-model="message" class="form-control" @keyup.enter="send" :disabled="$store.state.user.isGuest || !$store.state.options.showMessage" placeholder="<?php echo Yii::app()->user->isGuest ? Yii::t('common', 'Please login.') : ''; ?>" />
        </div>
        <div class="col-sm-2 col-lg-1">
          <button class="btn btn-primary btn-md form-control" @click="send" :disabled="$store.state.user.isGuest || !$store.state.options.showMessage || message == ''"><?php echo Yii::t('common', 'Submit'); ?></button>
        </div>
      </div>
    </div>
  </div>
</template>

<template id="message-template">
  <div class="chat-message" :class="{'self-message': message.isSelf}">
    <div class="message-meta">
      {{message.user.name}} {{message.time | formatTime}}
    </div>
    <div class="message-body">
      {{{message.content}}}
    </div>
  </div>
</template>

<template id="result-template">
  <div class="row">
    <div class="col-md-3" v-if="hasPermission" v-show="options.enableEntry">
      <input-panel :result.sync="current"></input-panel>
    </div>
    <div class="col-md-{{hasPermission && options.enableEntry ? 9 : 12}}">
      <div class="clearfix">
        <h4 class="pull-left">{{eventName}} - {{roundName}}</h4>
        <div class="pull-right">
          <select @change="changeEventRound" v-model="eventRound">
            <optgroup v-for="event in events" :label="event.name">
              <option v-for="round in event.rounds" :value="{event: event.id, round: round.id}">
                {{event.name}} - {{round.name}}{{round.status != 0 ? ' - ' + round.allStatus[round.status] : ''}}
              </option>
            </optgroup>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-condensed table-hover table-boxed">
          <thead>
            <th v-if="hasPermission"></th>
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
            <tr v-if="loading" class="loading">
              <td colspan="{{hasPermission ? 9 : 8}}">
                Loading...
              </td>
            </tr>
            <tr v-for="result in results" :class="{danger: result.isNew, success: isAdvanced(result)}" @dblclick="edit(result)">
              <td v-if="hasPermission">
                <button class="btn btn-xs btn-primary no-mr" @click="edit(result)"><i class="fa fa-edit"></i></button>
              </td>
              <td>{{result.pos}}</td>
              <td>
                <a href="javascript:void(0)" @click="goToUser(result.user)">{{result.user.name}}</a>
              </td>
              <td class="result">{{result.best | decodeResult result.event}}</td>
              <td class="record">{{result.regional_single_record}}</td>
              <td class="result">{{result.average | decodeResult result.event}}</td>
              <td class="record">{{result.regional_average_record}}</td>
              <td>{{{result.user.region}}}</td>
              <td>
                {{result.value1 | decodeResult result.event '--'}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value2 | decodeResult result.event '--'}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value3 | decodeResult result.event '--'}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value4 | decodeResult result.event '--'}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value5 | decodeResult result.event '--'}}&nbsp;&nbsp;&nbsp;&nbsp;
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<template id="input-panel-template">
  <div data-spy="affix" data-offset-top="550" style="top:20px">
    <div class="panel panel-theme input-panel">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo Yii::t('live', 'Input Panel'); ?></h3>
      </div>
      <div class="panel-body">
        <label for="input-panel-name"><?php echo Yii::t('common', 'Competitor'); ?></label> {{competitor && competitor.name}}
        <div class="input-wrapper">
          <input type="text"
            id="input-panel-name"
            class="form-control"
            placeholder="请输入编号或姓名"
            v-model="name"
            @keydown.enter="enter"
            @keydown.down="down"
            @keydown.up="up"
            @focus="searching = true"
            @blur="searching = false"
          >
          <ul class="competitors list-group" :class="{hide: !searching}">
            <li v-for="result in competitors"
              class="list-group-item"
              :class="{active: selectedIndex == $index}"
              @mousedown="selectCompetitor(result)"
              @mouseenter="selectedIndex = $index"
            >{{result.user.name}}</li>
          </ul>
        </div>
        <label><?php echo Yii::t('common', 'Results'); ?></label>
        <div class="input-panel-result">
          <result-input v-for="i in inputNum"
            :value.sync="$data['value' + (i + 1)]"
            :index="i"
          ></result-input>
        </div>
        <button type="button"
          @click="save"
          @keydown.enter.prevent="save"
          :disabled="result == null || result.id == null"
        ><?php echo Yii::t('live', 'Save'); ?></button>
      </div>
    </div>
  </div>
</template>

<template id="result-input-template">
  <div class="input-group">
    <span class="input-group-addon">{{index + 1}}.</span>
    <input class="form-control" type="tel"
      v-model="display"
      @focus="focus($event)"
      @blur="blur"
      @keydown.prevent="keydown"
      :disabled="$parent.isDisabled(index)"
    >
  </div>
</template>