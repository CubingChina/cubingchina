<?php $events = $competition->getEventsRounds(); ?>
<?php $params = $competition->getLastActiveEventRound($events); ?>
<?php echo CHtml::tag('div', array(
  'id'=>'live-container',
  'data-competition-id'=>$competition->id,
  'data-events'=>json_encode($events),
  'data-params'=>json_encode($params),
  'data-filters'=>json_encode(array(
    array(
      'label'=>Yii::t('common', 'All'),
      'value'=>'all',
    ),
    array(
      'label'=>Yii::t('live', 'Females'),
      'value'=>'females',
    ),
    array(
      'label'=>Yii::t('live', 'Children'),
      'value'=>'children',
    ),
    array(
      'label'=>Yii::t('live', 'New Comers'),
      'value'=>'newcomers',
    ),
  )),
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
    <div class="options-area">
      <button class="btn btn-md btn-warning no-mr" @click="showOptions">
        <i class="fa fa-gear"></i>
      </button>
      <div class="online-number">
        <?php echo Yii::t('live', 'Online: '); ?>{{onlineNumber}}
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
              <div class="checkbox hide">
                <label>
                  <input type="checkbox" v-model="options.alertRecord"> <?php echo Yii::t('live', 'Show Record on Chat'); ?>
                </label>
              </div>
              <hr v-if="hasPermission">
              <?php $form = $this->beginWidget('ActiveForm', array(
                'htmlOptions'=>array(
                  'class'=>'form-horizontal',
                  'v-if'=>'hasPermission',
                ),
                'action'=>array('/board/registration/exportLiveData', 'id'=>$competition->id),
              )); ?>
              <input type="hidden" value="1" name="xlsx">
              <button type="submit" class="btn btn-theme"><?php echo Yii::t('live', 'Export'); ?></button>
              <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button data-dismiss="modal" class="btn btn-default" type="button"><?php echo Yii::t('common', 'Close'); ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div tabindex="-1" id="user-results-modal" class="modal fade">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-body">
            <div class="well well-sm">
              <a href="/results/person/{{currentUser.wcaid}}" v-if="currentUser.wcaid">{{currentUser.name}}</a>
              <span v-else>{{currentUser.name}}</span>
              - {{currentUser.region}}
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-condensed table-hover table-boxed">
                <thead>
                  <th><?php echo Yii::t('Results', 'Round'); ?></th>
                  <th><?php echo Yii::t('Results', 'Place'); ?></th>
                  <th class="text-right"><?php echo Yii::t('common', 'Best'); ?></th>
                  <th class="text-right"><?php echo Yii::t('common', 'Average'); ?></th>
                  <th><?php echo Yii::t('common', 'Detail'); ?></th>
                </thead>
                <tbody>
                  <tr v-if="loadingUserResults" class="loading">
                    <td colspan="8">
                      Loading...
                    </td>
                  </tr>
                  <tr v-for="result in userResults">
                    <td colspan="8" v-if="result.type == 'event'">{{getEventName(result.event)}}</td>
                    <td v-if="result.type == 'result'">{{getRoundName(result.event, result.round)}}</td>
                    <td v-if="result.type == 'result'">{{result.pos}}</td>
                    <td v-if="result.type == 'result'" class="text-right">
                      <span class="record" v-if="result.regional_single_record" :class="getRecordClass(result.regional_single_record)">
                        {{result.regional_single_record}}
                      </span>
                      <span :class="{'new-best': result.newBest}">
                        {{result.best | decodeResult result.event}}
                      </span>
                    </td>
                    <td v-if="result.type == 'result'" class="text-right">
                      <span class="record" v-if="result.regional_average_record" :class="getRecordClass(result.regional_average_record)">
                        {{result.regional_average_record}}
                      </span>
                      <span :class="{'new-best': result.newAverage}">
                        {{result.average | decodeResult result.event}}
                      </span>
                    </td>
                    <td v-if="result.type == 'result'">
                      {{result.value1 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                      {{result.value2 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                      {{result.value3 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                      {{result.value4 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                      {{result.value5 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button data-dismiss="modal" class="btn btn-default" type="button"><?php echo Yii::t('common', 'Close'); ?></button>
          </div>
        </div>
      </div>
    </div>
    <chat :options="options" v-if="options.showMessage || options.alertResult || options.alertRecord"></chat>
    <result :options="options"></result>
  </div>
</template>

<template id="chat-template">
  <div class="panel panel-info">
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
          <input v-model="message" class="form-control" @keyup.enter="send" :disabled="$store.state.user.isGuest || !options.showMessage" placeholder="<?php echo Yii::app()->user->isGuest ? Yii::t('common', 'Please login.') : ''; ?>" />
        </div>
        <div class="col-sm-2 col-lg-1">
          <button class="btn btn-theme btn-md" @click="send" :disabled="$store.state.user.isGuest || !options.showMessage || message == ''"><?php echo Yii::t('common', 'Submit'); ?></button>
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
    <div class="col-md-3 col-sm-4" :class="{hide: !hasPermission || !options.enableEntry}">
      <input-panel :result.sync="current"></input-panel>
    </div>
    <div class="col-md-{{hasPermission && options.enableEntry ? 9 : 12}} col-sm-{{hasPermission && options.enableEntry ? 8 : 12}}">
      <div tabindex="-1" id="round-settings-modal" class="modal fade">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <div class="form-group">
                <label><?php echo Yii::t('Schedule', 'Cut Off'); ?></label>
                <input type="tel" class="form-control" id="cut_off" v-model="cut_off">
              </div>
              <div class="form-group">
                <label><?php echo Yii::t('Schedule', 'Time Limit'); ?></label>
                <input type="tel" class="form-control" id="time_limit" v-model="time_limit">
              </div>
              <div class="form-group">
                <label><?php echo Yii::t('Schedule', 'Competitors'); ?></label>
                <input type="tel" class="form-control" id="number" v-model="number">
              </div>
              <div class="form-group">
                <button type="button"
                  class="btn btn-sm btn-danger"
                  v-if="hasPermission && options.enableEntry && isCurrentRoundOpen"
                  @click="closeRound"
                >
                  <i class="fa fa-times"></i><?php echo Yii::t('live', 'Close this round'); ?>
                </button>
              </div>
              <div class="form-group">
                <button type="button"
                  class="btn btn-sm btn-warning"
                  v-if="hasPermission && options.enableEntry && isCurrentRoundOpen"
                  @click="resetCompetitors"
                >
                  <i class="fa fa-repeat"></i><?php echo Yii::t('live', 'Reset competitors'); ?>
                </button>
              </div>
              <div class="form-group">
                <button type="button"
                  class="btn btn-sm btn-success"
                  v-if="hasPermission && options.enableEntry && !isCurrentRoundOpen"
                  @click="openRound"
                >
                  <i class="fa fa-check"></i><?php echo Yii::t('live', 'Open this round'); ?>
                </button>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-theme" type="button" @click="saveRoundSettings"><?php echo Yii::t('live', 'Save'); ?></button>
              <button data-dismiss="modal" class="btn btn-default" type="button"><?php echo Yii::t('common', 'Close'); ?></button>
            </div>
          </div>
        </div>
      </div>
      <div class="clearfix">
        <h4 class="pull-left">
          {{eventName}} - {{roundName}}
          <button type="button"
            class="btn btn-sm btn-warning no-mr"
            v-if="hasPermission && options.enableEntry"
            @click="showRoundSettings"
          >
            <i class="fa fa-gear"></i>
          </button>
        </h4>
        <div class="pull-right event-round-area">
          <select @change="changeParams" v-model="eventRound">
            <optgroup v-for="event in events" :label="event.name">
              <option v-for="round in event.rounds" :value="{event: event.id, round: round.id}">
                {{event.name}} - {{round.name}}{{round.status != 0 ? ' - ' + round.allStatus[round.status] : ''}}
              </option>
            </optgroup>
          </select>
          <select @change="changeParams" v-model="filter">
            <option v-for="filter in filters" :value="filter.value">
              {{filter.label}}
            </option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-condensed table-hover table-boxed">
          <thead>
            <th v-if="hasPermission && isCurrentRoundOpen"></th>
            <th><?php echo Yii::t('Results', 'Place'); ?></th>
            <th><?php echo Yii::t('Results', 'Person'); ?></th>
            <th class="text-right"><?php echo Yii::t('common', 'Best'); ?></th>
            <th class="text-right" v-if="hasAverage()"><?php echo Yii::t('common', 'Average'); ?></th>
            <th><?php echo Yii::t('common', 'Region'); ?></th>
            <th><?php echo Yii::t('common', 'Detail'); ?></th>
          </thead>
          <tbody>
            <tr v-if="loading" class="loading">
              <td colspan="{{hasPermission ? 9 : 8}}">
                Loading...
              </td>
            </tr>
            <tr v-for="result in results | limitBy limit offset" :class="{danger: result.isNew, success: isAdvanced(result)}" @dblclick="edit(result)">
              <td v-if="hasPermission && isCurrentRoundOpen">
                <button class="btn btn-xs btn-theme no-mr" @click="edit(result)"><i class="fa fa-edit"></i></button>
              </td>
              <td>{{result.pos}}</td>
              <td>
                <a href="javascript:void(0)" @click="goToUser(result.user)">{{result.user.name}}</a>
              </td>
              <td class="text-right">
                <span class="record" v-if="result.regional_single_record" :class="getRecordClass(result.regional_single_record)">
                  {{result.regional_single_record}}
                </span>
                {{result.best | decodeResult result.event}}
              </td>
              <td class="text-right" v-if="hasAverage(result)">
                <span class="record" v-if="result.regional_average_record" :class="getRecordClass(result.regional_average_record)">
                  {{result.regional_average_record}}
                </span>
                {{result.average | decodeResult result.event}}
              </td>
              <td>{{{result.user.region}}}</td>
              <td>
                {{result.value1 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value2 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value3 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value4 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
                {{result.value5 | decodeResult result.event}}&nbsp;&nbsp;&nbsp;&nbsp;
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <ul class="pagination" v-if="totalPage > 1">
        <li v-for="i in totalPage" class="page" :class="{active: i == page - 1}">
          <a href="javascript: void(0);" @click="page = i + 1">{{i + 1}}</a>
        </li>
      </ul>
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
          <div class="input-group">
            <span class="input-group-addon">No.</span>
            <input type="text"
              id="input-panel-name"
              class="form-control"
              placeholder="请输入编号或姓名"
              v-model="searchText"
              @keydown.enter="enter"
              @keydown.down="down"
              @keydown.up="up"
              @focus="searching = true"
              @blur="searching = false"
              :disabled="!$parent.isCurrentRoundOpen"
            >
          </div>
          <ul class="competitors list-group" :class="{hide: !searching}">
            <li v-for="result in competitors"
              class="list-group-item"
              :class="{active: selectedIndex == $index}"
              @mousedown.prevent="selectCompetitor(result)"
              @mouseenter="selectedIndex = $index"
            >
              <b class="number">No.{{result.number}}</b>{{result.user.name}}
            </li>
          </ul>
        </div>
        <label><?php echo Yii::t('common', 'Results'); ?></label>
        <div class="input-panel-result">
          <result-input v-for="i in inputNum"
            :value.sync="result['value' + (i + 1)]"
            :index="i"
          ></result-input>
        </div>
        <button type="button"
          id="save"
          class="btn btn-md btn-theme"
          @click="save"
          @keydown.prevent="keydown"
          :disabled="result == null || result.id == null"
        ><?php echo Yii::t('live', 'Save'); ?></button>
      </div>
    </div>
  </div>
</template>

<template id="result-input-template">
  <div class="input-group">
    <span class="input-group-addon">{{index + 1}}.</span>
    <template v-if="event == '333mbf'">
      <div class="form-control result-input-wrapper">
        <div class="result-input-wrapper col-xs-5"
          :class="{active: index == $parent.currentIndex && subIndex == 0, disabled: $parent.isDisabled(index)}"
        >
          <input class="result-input" type="tel"
            id="result-input-solved-{{index}}"
            v-model="solved"
            @focus="focus(0)"
            @blur="blur"
            @keydown.prevent="keydown($event, 'solved')"
            :disabled="$parent.isDisabled(index)"
          >
          <label for="result-input-solved-{{index}}">
            <span class="number-group" v-if="time != 'DNF' && time != 'DNS'">
              <span class="number" :class="{active: solved.length > 1}">{{solved.charAt(solved.length - 2) || 0}}</span>
              <span class="number" :class="{active: solved.length > 0}">{{solved.charAt(solved.length - 1) || 0}}</span>
            </span>
            <span class="penalty" v-else>{{time}}</span>
          </label>
        </div>
        <div class="result-input-wrapper col-xs-2":class="{disabled: $parent.isDisabled(index)}">
          <label class="text-center">
            <span>/</span>
          </label>
        </div>
        <div class="result-input-wrapper col-xs-5"
          :class="{active: index == $parent.currentIndex && subIndex == 1, disabled: $parent.isDisabled(index)}"
        >
          <input class="result-input" type="tel"
            id="result-input-tried-{{index}}"
            v-model="tried"
            @focus="focus(1)"
            @blur="blur"
            @keydown.prevent="keydown($event, 'tried')"
            :disabled="$parent.isDisabled(index)"
          >
          <label for="result-input-tried-{{index}}" class="text-left">
            <span class="number-group" v-if="time != 'DNF' && time != 'DNS'">
              <span class="number" :class="{active: tried.length > 1}">{{tried.charAt(tried.length - 2) || 0}}</span>
              <span class="number" :class="{active: tried.length > 0}">{{tried.charAt(tried.length - 1) || 0}}</span>
            </span>
            <span class="penalty" v-else>{{time}}</span>
          </label>
        </div>
      </div>
    </template>
    <div class="result-input-wrapper form-control"
      :class="{active: index == $parent.currentIndex && subIndex == 2, disabled: $parent.isDisabled(index)}"
    >
      <input class="result-input" type="tel"
        id="result-input-{{index}}"
        v-model="time"
        @focus="focus(2)"
        @blur="blur"
        @keydown.prevent="keydown($event, 'time')"
        :disabled="$parent.isDisabled(index)"
      >
      <label for="result-input-{{index}}" :class="{'text-center': event === '333mbf'}">
        <span class="number-group" v-if="time != 'DNF' && time != 'DNS'">
          <span class="number" :class="{active: time.length > 5}" v-if="event != '333fm' && event !='333mbf'">{{time.charAt(time.length - 6) || 0}}</span>
          <span class="number" :class="{active: time.length > 4}" v-if="event != '333fm' && event !='333mbf'">{{time.charAt(time.length - 5) || 0}}</span>
          <span class="number" :class="{active: time.length > 4}" v-if="event != '333fm' && event !='333mbf'">:</span>
          <span class="number" :class="{active: time.length > 3}" v-if="event != '333fm'">{{time.charAt(time.length - 4) || 0}}</span>
          <span class="number" :class="{active: time.length > 2}" v-if="event != '333fm'">{{time.charAt(time.length - 3) || 0}}</span>
          <span class="number" :class="{active: time.length > 2}" v-if="event != '333fm'">{{event !='333mbf' ? '.' : ':'}}</span>
          <span class="number" :class="{active: time.length > 1}">{{time.charAt(time.length - 2) || 0}}</span>
          <span class="number" :class="{active: time.length > 0}">{{time.charAt(time.length - 1) || 0}}</span>
        </span>
        <span class="penalty" v-else>{{time}}</span>
      </label>
    </div>
  </div>
</template>