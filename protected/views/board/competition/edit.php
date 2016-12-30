<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>比赛</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>比赛信息</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'clearfix row',
            ),
            'enableClientValidation'=>true,
          )); ?>
          <?php echo $form->errorSummary($model, null, null, array(
            'class'=>'text-danger col-lg-12',
          )); ?>
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#baseinfo" role="tab" data-toggle="tab">基本信息</a></li>
            <li role="presentation"><a href="#schedule" role="tab" data-toggle="tab">赛程安排</a></li>
            <li role="presentation"><a href="#regulation" role="tab" data-toggle="tab">比赛规则</a></li>
            <li role="presentation"><a href="#detail" role="tab" data-toggle="tab">比赛详情</a></li>
            <li role="presentation"><a href="#transportation" role="tab" data-toggle="tab">交通信息</a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="baseinfo">
              <?php if ($this->user->isOrganizer() && $model->isPublic()): ?>
              <div class="col-lg-12">
                <div class="alert alert-danger">该比赛已公示，基本信息不能修改，如需修改请联系<a href="mailto:admin@cubingchina.com"><i class="fa fa-envelope"></i>管理员</a></div>
              </div>
              <?php endif; ?>
              <?php echo Html::formGroup(
                $model, 'name_zh', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'name_zh', array(
                  'label'=>'中文名',
                )),
                Html::activeTextField($model, 'name_zh'),
                $form->error($model, 'name_zh', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'name', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'name', array(
                  'label'=>'英文名',
                )),
                Html::activeTextField($model, 'name'),
                $form->error($model, 'name', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'check_person', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'check_person', array(
                  'label'=>'报名自动审核' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'若选是，在未开启在线支付的状态下，选手报名后将会立刻通过审核，而不是进入待审列表',
                )),
                $form->dropDownList($model, 'check_person', $checkPersons, array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'check_person', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'type', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'type', array(
                  'label'=>'类型',
                )),
                $form->dropDownList($model, 'type', $types, array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'type', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'wca_competition_id', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'wca_competition_id', array(
                  'label'=>'WCA比赛ID',
                )),
                Html::activeTextField($model, 'wca_competition_id'),
                $form->error($model, 'wca_competition_id', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'entry_fee', array(
                  'class'=>'col-md-4'
                ),
                $form->labelEx($model, 'entry_fee', array(
                  'label'=>'基础报名费',
                )),
                Html::activeTextField($model, 'entry_fee'),
                $form->error($model, 'entry_fee', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'online_pay', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'online_pay', array(
                  'label'=>'在线支付' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'在线支付极大程度方便主办方的审核工作，手续费率大约是1.5%，详情请联系管理员',
                )),
                $form->dropDownList($model, 'online_pay', Competition::getOnlinePays(), array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'online_pay', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'person_num', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'person_num', array(
                  'label'=>'人数限制',
                )),
                Html::activeTextField($model, 'person_num'),
                $form->error($model, 'person_num', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'second_stage_date', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'second_stage_date', array(
                  'label'=>'第二阶段时间' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'不采用分阶段报名费的比赛忽略此项',
                )),
                Html::activeTextField($model, 'second_stage_date', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                )),
                $form->error($model, 'second_stage_date', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'second_stage_ratio', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'second_stage_ratio', array(
                  'label'=>'第二阶段倍率',
                )),
                Html::activeTextField($model, 'second_stage_ratio'),
                $form->error($model, 'second_stage_ratio', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'second_stage_all', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'second_stage_all', array(
                  'label'=>'包含所有项目',
                )),
                $form->dropDownList($model, 'second_stage_all', Competition::getYesOrNo(), array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'second_stage_all', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'third_stage_date', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'third_stage_date', array(
                  'label'=>'第三阶段时间' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'不采用分阶段报名费的比赛忽略此项',
                )),
                Html::activeTextField($model, 'third_stage_date', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                )),
                $form->error($model, 'third_stage_date', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'third_stage_ratio', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'third_stage_ratio', array(
                  'label'=>'第三阶段倍率',
                )),
                Html::activeTextField($model, 'third_stage_ratio'),
                $form->error($model, 'third_stage_ratio', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'local_type', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'local_type', array(
                  'label'=>'人数统计选项',
                )),
                $form->dropDownList($model, 'local_type', Competition::getLocalTypes(), array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'local_type', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'date', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'date', array(
                  'label'=>'日期',
                )),
                Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR) ? $form->labelEx($model, 'tba', array(
                  'label'=>$form->checkBox($model, 'tba') . '待定',
                )) : '',
                Html::activeTextField($model, 'date', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd',
                  'data-min-view'=>'2',
                )),
                $form->error($model, 'date', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'end_date', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'end_date', array(
                  'label'=>'结束日期',
                )),
                Html::activeTextField($model, 'end_date', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd',
                  'data-min-view'=>'2',
                )),
                $form->error($model, 'end_date', array('class'=>'text-danger'))
              );?>
              <div class="clearfix hidden-lg"></div>
              <?php echo Html::formGroup(
                $model, 'reg_start', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'reg_start'),
                Html::activeTextField($model, 'reg_start', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                  'placeholder'=>'留空默认公示后即开放报名',
                )),
                $form->error($model, 'reg_start', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'reg_end', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'reg_end'),
                Html::activeTextField($model, 'reg_end', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                )),
                $form->error($model, 'reg_end', array('class'=>'text-danger'))
              );?>
              <?php
              if ($model->isOld()) {
                echo Html::formGroup(
                  $model, 'organizers', array(
                    'class'=>'col-lg-12',
                  ),
                  $form->labelEx($model, 'oldOrganizer', array(
                    'label'=>'主办方',
                  )),
                  Html::activeTextField($model, 'oldOrganizerZh'),
                  $form->error($model, 'oldOrganizerZh', array('class'=>'text-danger')),
                  Html::activeTextField($model, 'oldOrganizer'),
                  $form->error($model, 'oldOrganizer', array('class'=>'text-danger'))
                );
              } else {
                echo Html::formGroup(
                  $model, 'organizers', array(
                    'class'=>'col-lg-12',
                  ),
                  $form->labelEx($model, 'organizers', array(
                    'label'=>'主办方',
                  )),
                  $form->checkBoxList($model, 'organizers', CHtml::listData($organizers, 'id', 'name_zh'), array(
                    'uncheckValue'=>'',
                    'container'=>'div',
                    'separator'=>'',
                    'class'=>'form-control organizer',
                    'labelOptions'=>array(
                      'class'=>'checkbox-inline hidden',
                    ),
                    'template'=>'{beginLabel}{input}{labelTitle}{endLabel}',
                  )),
                  CHtml::textField('', '', array(
                    'class'=>'form-control tokenfield',
                    'placeholder'=>'输入名字或拼音',
                  )),
                  $form->error($model, 'organizers', array('class'=>'text-danger'))
                );
              } ?>
              <?php echo Html::formGroup(
                $model, 'delegates', array(
                  'class'=>'col-lg-12',
                ),
                $form->labelEx($model, 'delegates', array(
                  'label'=>'代表',
                )),
                !$model->isOld() ? '' : implode('', array(
                  CHtml::tag('span', array(), $model->old->delegate_zh),
                  Html::activeTextField($model, 'oldDelegateZh'),
                  $form->error($model, 'oldDelegateZh', array('class'=>'text-danger')),
                  Html::activeTextField($model, 'oldDelegate'),
                  $form->error($model, 'oldDelegate', array('class'=>'text-danger')),
                )),
                $form->checkBoxList($model, 'delegates', $wcaDelegates, array(
                  'uncheckValue'=>'',
                  'baseID'=>'wca_delegates',
                  'container'=>'div',
                  'separator'=>'',
                  'class'=>'form-control',
                  'labelOptions'=>array(
                    'class'=>'checkbox-inline',
                  ),
                  'template'=>'{beginLabel}{input}{labelTitle}{endLabel}',
                )),
                $form->checkBoxList($model, 'delegates', $ccaDelegates, array(
                  'uncheckValue'=>null,
                  'container'=>'div',
                  'baseID'=>'cca_delegates',
                  'separator'=>'',
                  'class'=>'form-control',
                  'labelOptions'=>array(
                    'class'=>'checkbox-inline',
                  ),
                  'template'=>'{beginLabel}{input}{labelTitle}{endLabel}',
                )),
                $form->error($model, 'delegates', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'locations', array(
                  'class'=>'col-lg-12',
                ),
                $this->widget('MultiLocations', array(
                  'model'=>$model,
                  'cities'=>$cities,
                  'delegates'=>$wcaDelegates,
                ), true),
                $form->error($model, 'locations', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'events',array(
                  'class'=>'col-lg-12',
                ),
                $form->labelEx($model, 'events', array(
                  'label'=>'项目',
                )),
                CHtml::tag('button ', array(
                  'class'=>'btn btn-xs btn-primary',
                  'type'=>'button',
                  'data-toggle'=>'collapse',
                  'data-target'=>'#fee-desc',
                ) , Html::fontAwesome('info-circle', 'a') . '费用设置说明'),
                '<div class="collapse" id="fee-desc">
                  <div class="well">
                    各项目均可设置最多三个阶段的报名费，通常只需要填写第一阶段，当且仅当上述分阶段报名费时间设置时，此处的项目费用会生效，并且会覆盖上方的倍率。
                    <br>
                    意即如设置了分阶段报名费，那么该项目在第二或第三阶段的报名费下面设置的<b>优先权高于</b>上面设置的倍率。如果项目单独设置第二或第三阶段费用，则按此处下面设置，否则按上方倍率设置。
                    <br>
                    举例说明，设置第二阶段倍率为1.5，设置三阶第一阶段报名费为10，第二阶段为12，设置四阶第一阶段报名费为20，第二阶段留空，那么到达第二阶段时，三阶报名费为12，四阶为20×1.5=30。
                    <br>
                    <span class="text-danger">注意：第一阶段不写或写0表示报名费为0，第二或第三阶段表示不单独设置此项。</span>
                  </div>
                </div>',
                '<div class="row"><div class="col-lg-12"><strong>常规项目</strong></div></div>',
                $this->widget('EventsForm', array(
                  'model'=>$model,
                  'name'=>'events',
                  'events'=>$normalEvents,
                  'type'=>'range',
                  'numberOptions'=>array(
                    'min'=>0,
                    'max'=>4,
                    'class'=>'round-number-input',
                  ),
                  'feeOptions'=>array(
                    'min'=>0,
                  ),
                ), true),
                '<div class="row"><div class="col-lg-12"><strong>其它项目</strong></div></div>',
                $this->widget('EventsForm', array(
                  'model'=>$model,
                  'name'=>'events',
                  'events'=>$otherEvents,
                  'type'=>'range',
                  'numberOptions'=>array(
                    'min'=>0,
                    'max'=>4,
                    'class'=>'round-number-input',
                  ),
                  'feeOptions'=>array(
                    'min'=>0,
                  ),
                ), true),
                $form->error($model, 'events', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="schedule">
              <?php echo Html::formGroup(
                $model, 'schedules', array(
                  'class'=>'col-lg-12',
                ),
                $form->labelEx($model, 'schedules', array(
                  'label'=>'赛程',
                )),
                '<div class="text-danger">时间会自动排序，留空时间即可删除某项，无分组请留空</div>',
                $this->widget('SchedulesForm', array(
                  'model'=>$model,
                  'name'=>'schedules',
                ), true),
                $form->error($model, 'schedules', array('class'=>'text-danger'))
              );?>
            </div>
            <div role="tabpanel" class="tab-pane" id="regulation">
              <?php echo Html::formGroup(
                $model, 'regulations_zh', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'regulations_zh', array(
                  'label'=>'中文规则',
                )),
                $form->textArea($model, 'regulations_zh', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'regulations_zh', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'regulations', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'regulations', array(
                  'label'=>'英文规则',
                )),
                $form->textArea($model, 'regulations', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'regulations', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="detail">
              <?php echo Html::formGroup(
                $model, 'information_zh', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'information_zh', array(
                  'label'=>'中文详情',
                )),
                $form->textArea($model, 'information_zh', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'information_zh', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'information', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'information', array(
                  'label'=>'英文详情',
                )),
                $form->textArea($model, 'information', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'information', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
            </div>
            <div role="tabpanel" class="tab-pane" id="transportation">
              <?php echo Html::formGroup(
                $model, 'travel_zh', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'travel_zh', array(
                  'label'=>'中文交通信息',
                )),
                $form->textArea($model, 'travel_zh', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'travel_zh', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'travel', array(
                  'class'=>'col-lg-6',
                ),
                $form->labelEx($model, 'travel', array(
                  'label'=>'英文交通信息',
                )),
                $form->textArea($model, 'travel', array(
                  'class'=>'editor form-control'
                )),
                $form->error($model, 'travel', array('class'=>'text-danger'))
              );?>
            </div>
          </div>
          <div class="col-lg-12">
            <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Submit'); ?></button>
          </div>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$this->widget('Editor');
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerPackage('tokenfield');
$allCities = json_encode($cities);
$tokens = json_encode(array_map(function($organizer) {
  return array(
    'value'=>$organizer->user->id . '-' . $organizer->user->name_zh,
    'label'=>$organizer->user->name_zh,
  );
}, $model->organizer));
$datum = json_encode(array_map(function($user) {
  return array(
    'full'=>$user->getCompetitionName() . ' ' . $user->id,
    'value'=>$user->id . '-' . $user->name_zh,
    'label'=>$user->name_zh,
  );
}, $organizers));
$organizerNames = json_encode(CHtml::listData($organizers, 'id', 'name_zh'));
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  $('[data-toggle="tooltip"]').tooltip();
  $('.datetime-picker').on('mousedown touchstart', function() {
    $(this).datetimepicker({
      autoclose: true
    });
  });
  var allCities = {$allCities};
  $(document).on('change', '.province', function() {
    var city = $(this).parents('.location').find('.city'),
      cities = allCities[$(this).val()] || [];
    city.empty();
    $('<option value="">').appendTo(city);
    $.each(cities, function(id, name) {
      $('<option>').val(id).text(name).appendTo(city);
    });
    if (city.find('option').length == 2) {
      city.find('option:last').prop('selected', true);
    }
  }).on('change', '#Competition_type', function() {
    var type = $(this).val();
    if (type === 'WCA') {
      $('#wca_delegates').show();
      $('#cca_delegates').hide();
    } else {
      $('#cca_delegates').show();
      $('#wca_delegates').hide();
    }
  }).on('keydown', '.token-input', function(e) {
    if (e.which == 13) {
      e.preventDefault();
    }
  }).on('changeDate', '#Competition_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_end_date').datetimepicker('setStartDate', date);
    date.setDate(date.getDate() - 1);
    date.setHours(23);
    date.setMinutes(59);
    $('#Competition_reg_start').datetimepicker('setEndDate', date);
    $('#Competition_reg_end').datetimepicker('setEndDate', date);
  }).on('changeDate', '#Competition_reg_start', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_reg_end', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
  }).on('changeDate', '#Competition_second_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_third_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
  }).on('change', '.round-number-input', function () {
    var matches = $(this).attr('name').match(/\[events\]\[(.*?)\]/);
    if (!matches) {
      return true;
    }
    var event = matches[1],
        allAvailableEvents = $(document).data('allAvailableEvents'),
        beforeVal = allAvailableEvents[event],
        afterVal = $(this).val(),
        optionSelector = '.schedule-event option[value=' + event + ']';
    allAvailableEvents[event] = afterVal;
    $(document).data('allAvailableEvents', allAvailableEvents);
    if (afterVal > beforeVal) {
      $(optionSelector).prop('disabled', false);
    } else {
      var eventScheduleInfo = $(document).data('eventScheduleInfo');
      if (eventScheduleInfo[event] !== undefined && eventScheduleInfo[event] >= afterVal) {
        $(optionSelector).prop('disabled', true);
        eventScheduleInfo[event] = afterVal;
        $(document).data('eventScheduleInfo', eventScheduleInfo);
        var toDeleteCount = eventScheduleInfo[event] - afterVal;
        $(optionSelector + ':selected').each(function (i) {
          if (i > afterVal - 1) {
            $(this).parent().parent().parent().remove();
          }
        });
      } else if (afterVal == 0) {
        $(optionSelector).prop('disabled', true);
      } else {
        $(optionSelector).prop('disabled', false);
      }
    }
  });
  $('#Competition_date').trigger('changeDate');
  $('#Competition_reg_start').trigger('changeDate');
  $('#Competition_reg_end').trigger('changeDate');
  $('#Competition_type').trigger('change');
  var organizers = {$organizerNames};
  var engine = new Bloodhound({
    local: {$datum},
    datumTokenizer: function(d) {
      return d.full.split('');
    },
    queryTokenizer: function(d) {
      return d.split('');
    }
  });
  engine.initialize();
  $('.tokenfield').tokenfield({
    tokens: {$tokens},
    typeahead: [
      null,
      {
        source: engine.ttAdapter()
      }
    ]
  }).on('tokenfield:createtoken', function(e) {
    var id = e.attrs.value.split('-')[0];
    if (!organizers[id] || organizers[id] != e.attrs.value.split('-')[1]) {
      e.preventDefault();
    }
    //防止重复的
    $.each($(this).tokenfield('getTokens'), function(index, token) {
      if (token.value === e.attrs.value) {
        e.preventDefault();
        return false;
      }
    });
    if (e.attrs.value == e.attrs.label) {
      e.attrs.label = e.attrs.value.split('-')[1];
    }
  }).on('tokenfield:createdtoken', function(e) {
    $('input.organizer[value="' + e.attrs.value.split('-')[0] + '"]').prop('checked', true);
  }).on('tokenfield:removedtoken', function(e) {
    $('input.organizer[value="' + e.attrs.value.split('-')[0] + '"]').prop('checked', false);
  }).on('tokenfield:edittoken', function(e) {
    e.preventDefault();
  });
EOT
);