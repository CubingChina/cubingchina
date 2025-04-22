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
          <div class="col-sm-12">
            <p class="text-danger">
              <b>友情提示</b>：比赛信息可以多次编辑，请注意保存。
            </p>
            <?php if ($model->isNewRecord): ?>
            <p class="lead">此处是赛事主办比赛申请页面，不是选手报名页面，请注意！<br>如需报名参加比赛，请移步<?php echo CHtml::link('赛事页面', ['/competition/index']); ?>。</p>
            <?php endif; ?>
            <p class="lead">第一次申请请仔细阅读<?php echo CHtml::link('申请流程', ['/faq/index',
              'category_id'=>2,
              '#'=>'faq-9',
            ], ['target'=>'_blank']); ?> ！</p>
          </div>
          <ul class="nav nav-tabs" role="tablist">
            <?php if ($isOrganizerEditable): ?>
            <li role="presentation"><a href="#baseinfo" role="tab" data-toggle="tab">基本信息</a></li>
            <?php endif; ?>
            <?php if ($model->isAccepted() || $this->user->isAdministrator() || Yii::app()->user->checkPermission('caqa_member')): ?>
            <?php if ($this->user->canEditCompetition($model)): ?>
            <li role="presentation"><a href="#detail" role="tab" data-toggle="tab">详情</a></li>
            <li role="presentation"><a href="#regulation" role="tab" data-toggle="tab">规则</a></li>
            <?php endif; ?>
            <li role="presentation"><a href="#transportation" role="tab" data-toggle="tab">交通</a></li>
            <li role="presentation"><a href="#other" role="tab" data-toggle="tab">其他</a></li>
            <?php if ($this->user->canEditCompetition($model)): ?>
            <li><?php echo CHtml::link('项目', ['/board/competition/event', 'id'=>$model->id], ['target'=>'_blank']); ?></li>
            <li><?php echo CHtml::link('赛程', ['/board/competition/schedule', 'id'=>$model->id], ['target'=>'_blank']); ?></li>
            <?php endif; ?>
            <?php if ($model->isPublic()): ?>
            <li><?php echo CHtml::link('报名', ['/board/registration/index', 'Registration'=>['competition_id'=>$model->id]], ['target'=>'_blank']); ?></li>
            <li><?php echo CHtml::link('支付', ['/board/pay/index', 'Pay'=>['type_id'=>$model->id]], ['target'=>'_blank']); ?></li>
            <?php endif; ?>
            <li><?php echo CHtml::link('预览', $model->getUrl(), ['target'=>'_blank']); ?></li>
            <?php endif; ?>
          </ul>
          <div class="tab-content">
            <?php if ($isOrganizerEditable): ?>
            <div role="tabpanel" class="tab-pane" id="baseinfo">
              <div class="col-lg-12">
                <div class="text-danger">请参阅<?php echo CHtml::link('粗饼网比赛名称规范试行版', '/static/naming conventions.pdf', ['target'=>'_blank']); ?>填写比赛名称。</div>
              </div>
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
                $model, 'person_num', array(
                  'class'=>'col-md-4',
                ),
                $form->labelEx($model, 'person_num', array(
                  'label'=>'人数限制',
                )),
                Html::activeTextField($model, 'person_num'),
                $form->error($model, 'person_num', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'competitor_limit_type', array(
                  'class'=>'col-md-2',
                ),
                $form->labelEx($model, 'competitor_limit_type', array(
                  'label'=>'按项目限制人数',
                )),
                Html::activeSwitch($model, 'competitor_limit_type'),
                $form->error($model, 'competitor_limit_type', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'newcomer', array(
                  'class'=>'col-md-2',
                ),
                $form->labelEx($model, 'newcomer', array(
                  'label'=>Html::link('WCA 新人月赛', 'https://cubing.com/faq#faq-14', ['target'=>'_blank']),
                )),
                Html::activeSwitch($model, 'newcomer'),
                $form->error($model, 'newcomer', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'auto_accept', array(
                  'class'=>'col-md-2',
                ),
                $form->labelEx($model, 'auto_accept', array(
                  'label'=>'报名自动审核' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'若选是，在未开启在线支付的状态下，选手报名后将会立刻通过审核，而不是进入待审列表',
                )),
                Html::activeSwitch($model, 'auto_accept'),
                $form->error($model, 'auto_accept', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'online_pay', array(
                  'class'=>'col-md-2',
                ),
                $form->labelEx($model, 'online_pay', array(
                  'label'=>'在线支付' . Html::fontAwesome('question-circle', 'b'),
                  'data-toggle'=>'tooltip',
                  'title'=>'在线支付极大程度方便主办方的审核工作，手续费率大约是1.5%，详情请联系管理员',
                )),
                Html::activeSwitch($model, 'online_pay'),
                $form->error($model, 'online_pay', array('class'=>'text-danger'))
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
                '<div id="fee-tip"></div>',
                $form->error($model, 'entry_fee', array('class'=>'text-danger'))
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
              <?php if ($this->user->isAdministrator() || $this->user->isDelegate() || Yii::app()->user->checkPermission('caqa')): ?>
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
              <?php endif; ?>
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
                Html::activeSwitch($model, 'second_stage_all'),
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
              <div class="clearfix"></div>
              <?php if ($model->isAccepted()): ?>
              <?php echo Html::formGroup(
                $model, 'refund_type', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'refund_type', array(
                  'label'=>'退赛退费比例',
                )),
                $form->dropDownList($model, 'refund_type', Competition::getRefundTypes(), array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'refund_type', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'cancellation_end_time', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'cancellation_end_time', [
                  'label'=>'退赛截止时间',
                ]),
                Html::activeTextField($model, 'cancellation_end_time', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                  'placeholder'=>'请务必早于报名结束时间至少一天',
                )),
                $form->error($model, 'cancellation_end_time', array('class'=>'text-danger'))
              );?>
              <div class="clearfix hidden-lg"></div>
              <?php echo Html::formGroup(
                $model, 'reg_reopen_time', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'reg_reopen_time', [
                  'label'=>'补报开始时间',
                ]),
                Html::activeTextField($model, 'reg_reopen_time', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                  'placeholder'=>'请务必早于报名结束时间至少半天',
                )),
                $form->error($model, 'reg_reopen_time', array('class'=>'text-danger'))
              );?>
              <?php endif; ?>
              <?php if ($model->has_qualifying_time): ?>
              <?php echo Html::formGroup(
                $model, 'qualifying_end_time', array(
                  'class'=>'col-lg-3 col-md-6',
                ),
                $form->labelEx($model, 'qualifying_end_time'),
                Html::activeTextField($model, 'qualifying_end_time', array(
                  'class'=>'datetime-picker',
                  'data-date-format'=>'yyyy-mm-dd hh:ii:00',
                )),
                $form->error($model, 'qualifying_end_time', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php endif; ?>
              <div class="clearfix"></div>
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
              } elseif ($model->isAccepted() || $this->user->isAdministrator() || Yii::app()->user->checkPermission('caqa')) {
                echo Html::formGroup(
                  $model, 'organizers', array(
                    'class'=>'col-lg-12',
                  ),
                  $form->labelEx($model, 'organizers', array(
                    'label'=>'主办方',
                  )),
                  $form->listBox(
                    $model,
                    'organizers',
                    $model->getOrganizerKeyValues($model->organizers),
                    [
                      'class'=>'organizer',
                      'data-organizer'=>1,
                      'multiple'=>true,
                      'placeholder'=>'输入名字或拼音',
                    ]
                  ),
                  $form->error($model, 'organizers', array('class'=>'text-danger'))
                );
                echo Html::formGroup(
                  $model, 'organizerTeamMembers', array(
                    'class'=>'col-lg-12',
                  ),
                  $form->labelEx($model, 'organizerTeamMembers', array(
                    'label'=>'主办团队成员',
                  )),
                  $form->listBox(
                    $model,
                    'organizerTeamMembers',
                    $model->getOrganizerKeyValues($model->organizerTeamMembers, 'user_id'),
                    [
                      'class'=>'organizer',
                      'multiple'=>true,
                      'placeholder'=>'输入名字或拼音',
                    ]
                  ),
                  '<div>以上成员在比赛公示后即可进行优先报名，每场比赛主办团队成员不超过比赛人数/100向上取整，最多为5人。</div>',
                  $form->error($model, 'organizerTeamMembers', array('class'=>'text-danger'))
                );
              } ?>
              <?php echo Html::formGroup(
                $model, 'delegates', array(
                  'class'=>'col-lg-12',
                  'id'=>'delegates',
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
                    'class'=>'checkbox-inline hide',
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
              <?php if ($model->isAccepted() || $this->user->isAdministrator() || Yii::app()->user->checkPermission('caqa')): ?>
              <div class="clearfix"></div>
              <hr>
              <div class="col-lg-12">
                <h5>其他选项</h5>
              </div>
              <?php foreach (Competition::getBaseOptions() as $key=>$value):?>
              <?php echo Html::formGroup(
                $model, $key, array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, $key, array(
                  'label'=>$value['label'],
                )),
                Html::activeSwitch($model, $key),
                $form->error($model, $key, array('class'=>'text-danger'))
              );?>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ($model->isAccepted() || $this->user->isAdministrator() || Yii::app()->user->checkPermission('caqa')): ?>
            <?php if ($this->user->canEditCompetition($model)): ?>
            <div role="tabpanel" class="tab-pane" id="detail">
              <?php $this->renderPartial('editorTips'); ?>
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
            <div role="tabpanel" class="tab-pane" id="regulation">
              <div class="col-lg-12">
                <p class="lead text-danger">
                  请在编辑完之后务必看清楚规则页面，比赛公示后不可再次编辑！
                </p>
                <p class="lead text-danger">
                  请在编辑完之后务必看清楚规则页面，比赛公示后不可再次编辑！
                </p>
                <p class="lead text-danger">
                  请在编辑完之后务必看清楚规则页面，比赛公示后不可再次编辑！
                </p>
              </div>
              <?php foreach (Competition::getOtherOptions() as $key=>$value):?>
                <?php if (isset($value['title']) || isset($value['subtitle'])): ?>
                  <div class="clearfix"></div>
                  <hr>
                  <div class="col-lg-12">
                    <?php if (isset($value['title'])): ?>
                      <h3><?php echo $value['title']; ?></h3>
                    <?php endif; ?>
                    <?php if (isset($value['subtitle'])): ?>
                      <h4><strong><?php echo $value['subtitle'] ?></strong></h4>
                    <?php endif; ?>
                    <?php if (isset($value['warning'])): ?>
                      <div class="text-danger">
                        <?php echo $value['warning']; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <?php echo Html::formGroup(
                $model, $key, array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, $key, array(
                  'label'=>$value['label'],
                )),
                !isset($value['type']) ? Html::activeSwitch($model, $key) : $form->textField($model, $key, [
                  'class'=>'form-control',
                ]),
                $form->error($model, $key, array('class'=>'text-danger'))
              );?>
              <?php endforeach; ?>
              <div class="clearfix"></div>
              <?php echo Html::formGroup(
                $model, 'podiumsEvents', array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, 'podiumsEvents', array(
                  'label'=>'非官方领奖台项目',
                )),
                $form->listBox($model, 'podiumsEvents', $model->eventsNames, array(
                  'class'=>'form-control',
                  'size'=>count($model->associatedEvents),
                  'multiple'=>true,
                )),
                $form->error($model, 'podiumsEvents', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'podiums_num', array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, 'podiums_num', array(
                  'label'=>'领奖台人数',
                )),
                $form->dropDownList($model, 'podiums_num', array_combine(range(3, 8), range(3, 8)), [
                  'class'=>'form-control',
                ]),
                $form->error($model, 'podiums_num', array('class'=>'text-danger'))
              );?>
              <div class="clearfix"></div>
              <?php $this->renderPartial('editorTips'); ?>
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
            <?php endif; ?>
            <div role="tabpanel" class="tab-pane" id="transportation">
              <?php $this->renderPartial('editorTips'); ?>
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
            <div role="tabpanel" class="tab-pane" id="other">
              <?php if ($model->isRegistrationEnded()): ?>
              <?php echo Html::formGroup(
                $model, 'live', array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, 'live', array(
                  'label'=>'开启直播',
                )),
                Html::activeSwitch($model, 'live'),
                $form->error($model, 'live', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                $model, 'live_stream_url', array(
                  'class'=>'col-lg-9',
                ),
                $form->labelEx($model, 'live_stream_url', array(
                  'label'=>'直播链接',
                )),
                Html::activeTextField($model, 'live_stream_url'),
                CHtml::tag('div', ['class'=>'help-text'], '该链接会展示在成绩直播页面'),
                $form->error($model, 'live_stream_url', array('class'=>'text-danger'))
              );?>
              <?php echo Html::formGroup(
                  $model, 'scoreTakers', array(
                    'class'=>'col-lg-12',
                  ),
                  $form->labelEx($model, 'scoreTakers', array(
                    'label'=>'成绩录入员',
                  )),
                  $form->listBox(
                    $model,
                    'scoreTakers',
                    $model->getOrganizerKeyValues($model->scoreTakers, 'user_id'),
                    [
                      'class'=>'organizer',
                      'multiple'=>true,
                      'placeholder'=>'输入名字或拼音',
                    ]
                  ),
                  '<div>仅限比赛期间录入成绩。</div>',
                  $form->error($model, 'scoreTakers', array('class'=>'text-danger'))
                );
              ?>
              <?php endif; ?>
              <?php echo Html::formGroup(
                $model, 'local_type', array(
                  'class'=>'col-md-3',
                ),
                $form->labelEx($model, 'local_type', array(
                  'label'=>'人数统计选项',
                )),
                $form->dropDownList($model, 'local_type', Competition::getLocalTypes(), array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'local_type', array('class'=>'text-danger'))
              );?>
            </div>
            <?php endif; ?>
          </div>
          <div class="clearfix"></div>
          <div class="col-lg-12">
            <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Save'); ?></button>
            <?php if ($this->user->canLock($model)): ?>
            <?php echo CHtml::tag('button', [
              'type'=>'submit',
              'name'=>'lock',
              'value'=>1,
              'class'=>'btn btn-warning btn-square',
              'data-message'=>Yii::t('Competition', 'After locking the competition, organizers are no longer able to edit the basic infomation and anyone can visit this competition via url. Please confirm it!'),
            ], Yii::t('common', 'Lock')); ?>
            <?php endif; ?>
            <?php if ($this->user->canHide($model)): ?>
              <?php echo CHtml::tag('button', [
              'type'=>'submit',
              'name'=>'hide',
              'value'=>1,
              'class'=>'btn btn-danger btn-square',
              'data-message'=>Yii::t('Competition', 'The competition will be hide. People can\' visit it without permission.'),
            ], Yii::t('common', 'Hide')); ?>
            <?php endif; ?>
            <?php if ($this->user->canAnnounce($model)): ?>
              <?php echo CHtml::tag('button', [
              'type'=>'submit',
              'name'=>'announce',
              'value'=>1,
              'class'=>'btn btn-green btn-square',
              'data-message'=>Yii::t('Competition', 'Do you confirm this can be announced?'),
            ], Yii::t('common', 'Announce')); ?>
            <?php endif; ?>
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
Yii::app()->clientScript->registerPackage('tagsinput');
$allCities = json_encode($cities);
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  $('[role="presentation"] a').first().tab('show');
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
      $('#delegates').show();
    } else {
      $('#delegates').hide().find('input').prop('checked', false);
    }
    makeFeeTips()
  }).on('keydown', '.token-input', function(e) {
    if (e.which == 13) {
      e.preventDefault();
    }
  }).on('changeDate', '#Competition_date', function() {
    makeFeeTips()
    var date = $(this).datetimepicker('getDate');
    $('#Competition_end_date').datetimepicker('setStartDate', date);
    date.setDate(date.getDate() - 1);
    date.setHours(23);
    date.setMinutes(59);
    $('#Competition_reg_start, #Competition_qualifying_end_time').datetimepicker('setEndDate', date);
    $('#Competition_reg_end').datetimepicker('setEndDate', date);
  }).on('changeDate', '#Competition_end_date', function() {
    makeFeeTips()
  }).on('changeDate', '#Competition_reg_start', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date, #Competition_qualifying_end_time, #Competition_cancellation_end_time').datetimepicker('setStartDate', new Date(+date + 86400000 * 7));
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_reg_end', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
    $('#Competition_cancellation_end_time').datetimepicker('setEndDate', new Date(+date - 86400000));
    $('#Competition_reg_reopen_time').datetimepicker('setEndDate', new Date(+date - 43200000));
  }).on('changeDate', '#Competition_second_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_third_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
  }).on('changeDate', '#Competition_cancellation_end_time', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_reg_reopen_time').datetimepicker('setStartDate', new Date(+date + 43200000));
  }).on('click', 'button[data-message]', function(e) {
    if (!confirm(this.dataset.message)) {
      e.preventDefault();
    }
  }).on('input', '#Competition_entry_fee', function() {
    makeFeeTips()
  });
  function makeFeeTips() {
    let fee = $('#Competition_entry_fee').val();
    let type = $('#Competition_type').val();
    let date = new Date($('#Competition_date').val());
    let endDate = new Date($('#Competition_end_date').val() || $('#Competition_date').val());
    let days = Math.ceil((endDate - date) / 86400000) + 1;
    let cubingFee = days * 2;
    let feeTips = `粗饼运营费：\${days}天x2元/天=\${cubingFee}元<br>`;
    if (type === 'WCA') {
      feeTips += `WCA运营费：\${fee}元x15%=\${(fee * 0.15).toFixed(2)}元<br>`;
    }
    feeTips += `主办实收约：\${fee - cubingFee - (type === 'WCA' ? (fee * 0.15).toFixed(2) : 0)}元（不含交易手续费）`;
    $('#fee-tip').html(feeTips);
  }
  $('#Competition_date').trigger('changeDate');
  $('#Competition_reg_start').trigger('changeDate');
  $('#Competition_reg_end').trigger('changeDate');
  $('#Competition_cancellation_end_time').trigger('changeDate');
  $('#Competition_type').trigger('change');

  // organizer team members
  $('.organizer').each(function() {
    var that = $(this);
    const isOrganizer = that.data('organizer');
    const [tagsinput] = that.on('itemAdded', function() {
      var that = $(this);
      setTimeout(function() {
        that.tagsinput('input').val('');
      }, 0);
    }).tagsinput({
      itemValue: function(user) {
        return user.id
      },
      itemText: function(user) {
        return [user.id, user.display_name].join('-')
      },
      maxTags: isOrganizer ? 0 : 5,
      freeInput: false,
      typeahead: {
        source: function(query) {
          return $.ajax({
            url: '/board/user/search',
            data: {
              query: query,
              organizer: isOrganizer ? 1 : 0
            },
            dataType: 'json'
          })
        }
      }
    })
    $.each(that.find('option'), function(index, option) {
      tagsinput.add({
        id: parseInt($(this).val()),
        display_name: $(this).text()
      })
    })
    tagsinput.\$container.css('display', 'block').find('input').attr('size', 20)
  })
EOT
);
if (!$model->isAccepted()) {
  $date = strtotime('+14 days') * 1000;
  Yii::app()->clientScript->registerScript('competition-date',
<<<EOT
  $('#Competition_date').datetimepicker('setStartDate', new Date({$date}));
EOT
);
}
