<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>查看申请资料</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>查看申请资料</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#baseinfo" role="tab" data-toggle="tab">基本信息</a></li>
            <li role="presentation"><a href="#organizer" role="tab" data-toggle="tab">主办团队</a></li>
            <li role="presentation"><a href="#schedule" role="tab" data-toggle="tab">赛程</a></li>
            <li role="presentation"><a href="#venue" role="tab" data-toggle="tab">场地</a></li>
            <li role="presentation"><a href="#sponsor" role="tab" data-toggle="tab">赞助</a></li>
            <li role="presentation"><a href="#other" role="tab" data-toggle="tab">其他</a></li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="baseinfo">
              <dl>
                <?php if ($competition->type == Competition::TYPE_WCA): ?>
                <dt>类型</dt>
                <dd><?php echo $competition->getTypeText(); ?></dd>
                <?php endif; ?>
                <?php if ($competition->wca_competition_id != ''): ?>
                <dt><?php echo Yii::t('Competition', 'WCA Official Page'); ?></dt>
                <dd><?php echo CHtml::link($competition->getWcaUrl(), $competition->getWcaUrl(), array('target'=>'_blank')); ?>
                <?php endif; ?>
                <dt><?php echo Yii::t('Competition', 'Date'); ?></dt>
                <dd><?php echo $competition->getDisplayDate(); ?></dd>
                <dt><?php echo Yii::t('Competition', 'Location'); ?></dt>
                <dd>
                  <?php $this->renderPartial('locations', $_data_); ?>
                </dd>
                <dt><?php echo Yii::t('Competition', 'Organizers'); ?></dt>
                <dd>
                  <?php if ($competition->isOld()): ?>
                  <?php echo OldCompetition::formatInfo($competition->old->getAttributeValue('organizer')); ?>
                  <?php else: ?>
                  <?php foreach ($competition->organizer as $key=>$organizer): ?>
                  <?php if ($key > 0) echo Yii::t('common', ', '); ?>
                  <?php echo CHtml::mailto(Html::fontAwesome('envelope', 'a') . $organizer->user->getAttributeValue('name', true), $organizer->user->email); ?>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </dd>
                <?php if ($competition->delegate !== array() && !$competition->multi_countries): ?>
                <dt><?php echo Yii::t('Competition', $competition->type == Competition::TYPE_WCA ? 'Delegates' : 'Main Judge'); ?></dt>
                <dd>
                  <?php foreach ($competition->delegate as $key=>$delegate): ?>
                  <?php if ($key > 0) echo Yii::t('common', ', '); ?>
                  <?php echo CHtml::mailto(Html::fontAwesome('envelope', 'a') . $delegate->user->getAttributeValue('name', true), $delegate->user->email); ?>
                  <?php endforeach; ?>
                </dd>
                <?php elseif ($competition->isOld() && $competition->old->getAttributeValue('delegate')): ?>
                <dt><?php echo Yii::t('Competition', $competition->type == Competition::TYPE_WCA ? 'Delegates' : 'Main Judge'); ?></dt>
                <dd>
                  <?php echo OldCompetition::formatInfo($competition->old->getAttributeValue('delegate')); ?>
                </dd>
                <?php endif; ?>
                <dt><?php echo Yii::t('Competition', 'Events'); ?></dt>
                <dd>
                  <?php echo implode(Yii::t('common', ', '), array_map(function($event) use ($competition) {
                    return Yii::t('event', $competition->getFullEventName($event));
                  }, array_keys($competition->getRegistrationEvents()))); ?>
                </dd>
                <?php if (!$competition->multi_countries): ?>
                <dt><?php echo Yii::t('Competition', 'Entry Fee'); ?></dt>
                <dd class="table-responsive">
                  <table class="table table-bordered table-hover table-condensed">
                    <thead>
                      <tr>
                        <th><?php echo Yii::t('Competition', 'Events'); ?></th>
                        <th><?php echo $competition->firstStage; ?></th>
                        <?php if ($competition->hasSecondStage): ?>
                        <th><?php echo $competition->secondStage; ?></th>
                        <?php endif; ?>
                        <?php if ($competition->hasThirdStage): ?>
                        <th><?php echo $competition->thirdStage; ?></th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php echo Yii::t('Competition', 'Base Entry Fee'); ?></td>
                        <td>　<i class="fa fa-rmb"></i><?php echo $competition->entry_fee; ?></td>
                        <?php if ($competition->hasSecondStage): ?>
                        <td>　<i class="fa fa-rmb"></i><?php echo $competition->getEventFee('entry', Competition::STAGE_SECOND); ?></td>
                        <?php endif; ?>
                        <?php if ($competition->hasThirdStage): ?>
                        <td>　<i class="fa fa-rmb"></i><?php echo $competition->getEventFee('entry', Competition::STAGE_THIRD); ?></td>
                        <?php endif; ?>
                      </tr>
                      <?php foreach ($competition->events as $key=>$value): ?>
                      <?php if ($value['round'] > 0): ?>
                      <tr>
                        <td><?php echo Events::getFullEventName($key); ?></td>
                        <td>&nbsp;+<i class="fa fa-rmb"></i><?php echo $value['fee']; ?></td>
                        <?php if ($competition->hasSecondStage): ?>
                        <td>&nbsp;+<i class="fa fa-rmb"></i><?php echo $competition->getEventFee($key, Competition::STAGE_SECOND); ?></td>
                        <?php endif; ?>
                        <?php if ($competition->hasThirdStage): ?>
                        <td>&nbsp;+<i class="fa fa-rmb"></i><?php echo $competition->getEventFee($key, Competition::STAGE_THIRD); ?></td>
                        <?php endif; ?>
                      </tr>
                      <?php endif; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </dd>
                <?php endif; ?>
                <?php if ($competition->person_num > 0): ?>
                <dt><?php echo Yii::t('Competition', 'Limited Number of Competitor'); ?></dt>
                <dd><?php echo $competition->person_num; ?></dd>
                <?php endif; ?>
                <?php if ($competition->reg_start > 0): ?>
                <dt><?php echo Yii::t('Competition', 'Registration Starting Time'); ?></dt>
                <dd><?php echo date('Y-m-d H:i:s', $competition->reg_start); ?></dd>
                <?php endif; ?>
                <dt><?php echo Yii::t('Competition', 'Registration Ending Time'); ?></dt>
                <dd><?php echo date('Y-m-d H:i:s', $competition->reg_end); ?></dd>
              </dl>
            </div>
            <div role="tabpanel" class="tab-pane" id="organizer">
              <dl>
                <dt>组织/参与过的比赛</dt>
                <dd class="information-container">
                  <?php echo $competition->application->organized_competition; ?>
                </dd>
                <dt>申请人自我阐述</dt>
                <dd class="information-container">
                  <?php echo $competition->application->self_introduction; ?>
                </dd>
                <dt>主办团队介绍</dt>
                <dd class="information-container">
                  <?php echo $competition->application->team_introduction; ?>
                </dd>
              </dl>
            </div>
            <div role="tabpanel" class="tab-pane" id="schedule">
              <dl>
                <dt>预估赛程</dt>
                <dd class="information-container">
                  <?php echo $competition->application->schedule; ?>
                </dd>
              </dl>
            </div>
            <div role="tabpanel" class="tab-pane" id="venue">
              <dl>
                <dt>场地</dt>
                <dd class="information-container">
                  <?php echo $competition->application->venue_detail; ?>
                </dd>
              </dl>
            </div>
            <div role="tabpanel" class="tab-pane" id="sponsor">
              <dl>
                <dt>预算</dt>
                <dd class="information-container">
                  <?php echo $competition->application->budget; ?>
                </dd>
                <dt>赞助</dt>
                <dd class="information-container">
                  <?php echo $competition->application->sponsor; ?>
                </dd>
              </dl>
            </div>
            <div role="tabpanel" class="tab-pane" id="other">
              <dl>
                <dt>其他</dt>
                <dd class="information-container">
                  <?php echo $competition->application->other; ?>
                </dd>
              </dl>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
