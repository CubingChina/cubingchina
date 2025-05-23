<h3>一、关于报名</h3>
<ol>
  <?php if ($competition->series): ?>
  <?php $otherCompetitions = implode('、', array_map(function($series) {
    $competition = $series->competition;
    return CHtml::link($competition->getAttributeValue('name'), $competition->getUrl());
  }, array_filter($competition->series->list, function($series) use ($competition) {
    return $series->competition_id != $competition->id;
  }))); ?>
  <li>
    <a href="https://cubing.com/faq#faq-12" target="_blank">WCA系列赛</a>报名要求：
    <ol>
      <li>比赛选手只能报名系列赛比赛中的一场。</li>
      <li>选手成功报名本场比赛后，选手将不能再报名<?php echo $otherCompetitions; ?>。</li>
      <li>如果想要报名<?php echo $otherCompetitions; ?>，需要先取消本场比赛的报名。</li>
    </ol>
  </li>
  <?php endif; ?>
  <li>
    比赛报名需在比赛报名期间（<?php echo date('Y年m月d日 H:i:s', $competition->reg_start), date('至Y年m月d日 H:i:s', $competition->reg_end); ?>）完成，逾期不接受报名。比赛只接受网上报名，不接受现场报名。
  </li>
  <li>
    报名需要选手在<a href="/">中国魔方赛事网</a>（即本网站）注册。
  </li>
  <?php if ($competition->hasSecondStage): ?>
  <?php $phase = 1; ?>
  <li>
    报名期限分为<b><?php echo 1 + $competition->hasSecondStage + $competition->hasThirdStage; ?></b>个阶段：第<?php
    echo $phase++; ?>阶段为<?php echo $competition->reg_start ? date('Y年m月d日 H:i:s', $competition->reg_start) : '即日起'; ?>至<?php echo date('Y年m月d日 H:i:s', $competition->second_stage_date - 1); ?><?php if ($competition->hasThirdStage): ?>；第<?php
    echo $phase++; ?>阶段时间为<?php echo date('Y年m月d日 H:i:s', $competition->second_stage_date); ?>至<?php echo date('Y年m月d日 H:i:s', $competition->third_stage_date - 1); ?><?php endif; ?>；第<?php
    echo $phase++; ?>阶段时间为<?php echo date('Y年m月d日 H:i:s', $competition->hasThirdStage ? $competition->third_stage_date : $competition->second_stage_date); ?>至<?php echo date('Y年m月d日 H:i:s', $competition->reg_end); ?>；第1阶段结束后报名费会有所上涨。
  </li>
  <?php endif; ?>
  <li>
    报名费用：应付报名费=基础报名费+<?php echo CHtml::link('分项报名费', $competition->getUrl('detail', ['#'=>'fees'])); ?>。<?php if ($competition->hasSecondStage): ?>第1阶段基础报名费为<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_FIRST) ;?>元；第2阶段基础报名费为<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_SECOND) ;?>元<?php
    if ($competition->hasThirdStage): ?>；第3阶段基础报名费为<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_THIRD) ;?>元<?php endif; ?><?php else: ?>基础报名费为<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY) ;?>元<?php endif; ?>。
  </li>
  <li>
    中国大陆选手须通过粗饼网的线上缴费功能缴纳报名费，不通过粗饼网付款的中国大陆选手报名一律无效。对于无法使用中国支付系统的非中国大陆选手<?php if ($competition->paypal_link): ?>，可通过PayPal支付报名费用，审核将有一定延迟<?php endif; ?>，有支付问题请邮件联系主办方。
  </li>
  <?php if ($competition->has_qualifying_time): ?>
  <li>
    <b>资格筛选：</b>
    <ol>
      <li>报名时选手只能选择已经达到资格线的项目，至少选择任意一个达到资格线的项目才可以完成报名；</li>
      <li>未达到资格线的项目不可勾选报名，选手在<?php echo  date('Y年m月d日 H:i', $competition->qualifying_end_time); ?>之前达到资格线后可自行增补项目，资格线见<?php echo CHtml::link('详情页', $competition->getUrl('detail', ['#'=>'events'])) ?>；</li>
      <li>项目成绩以<a href="https://www.worldcubeassociation.org/results/" target="_blank">WCA官方</a>公示的成绩为准。</li>
    </ol>
  </li>
  <?php endif; ?>
  <?php if ($competition->cancellation_end_time > 0) :?>
  <li>
    选手<b>退赛</b>：比赛选手报名后可以在指定日期内进行比赛退赛，退赛的截止时间为<?php echo date('Y年m月d日 H:i:s', $competition->cancellation_end_time); ?>。<?php if ($competition->refund_type !== Competition::REFUND_TYPE_NONE): ?>选手在完成退赛后，<?php echo $competition->refund_type; ?>%报名费将退回。<?php endif; ?>选手完成退赛后将不能再次报名本次比赛。
  </li>
  <?php endif; ?>
  <?php if ($competition->allow_change_event) :?>
  <li>
    项目<b>补报</b>：在报名结束即<?php echo date('Y年m月d日 H:i:s', $competition->reg_end); ?>之前，比赛选手可以通过粗饼网“项目增补”功能自行完成项目增补，项目增补的费用及其过程中可能出现的手续费由选手承担。
  </li>
  <?php endif; ?>
  <li>
    项目<b>更改</b>：本次比赛不接受更换、删除项目的请求，例如将四阶速拧改为二阶速拧的请求。也不接受选手替代的请求，例如用未报名的选手B替代已报名成功选手A的参赛资格。
  </li>
  <li>
    <b>费用退还</b>：<?php if ($competition->cancellation_end_time > 0 && $competition->refund_type !== Competition::REFUND_TYPE_NONE): ?>除选手主动通过粗饼网进行退赛操作返还<?php echo $competition->refund_type; ?>%报名费外，<?php endif; ?>所有途径上交的比赛报名费用一律不退还，请选手谨慎缴费。
  </li>
  <li>
    未满18周岁的选手必须在家长同意后方可报名。
  </li>
  <?php if ($competition->person_num > 0): ?>
  <li>
    本次比赛限制参赛人数为<?php echo $competition->person_num; ?>人。
  </li>
  <?php endif; ?>
</ol>
<h3>二、签到与入场</h3>
<ol>
  <li>
    选手需在指定时间段进行签到，必须选手本人签到，不可由他人代签。
  </li>
  <li>
    请选手按指示标牌进行签到，非参赛人员禁止进入选手签到区，以免造成混乱，扰乱签到秩序。
  </li>
  <li>
    选手本人需出示<?php if ($competition->show_qrcode): ?>报名成功二维码 （登录粗饼网后在<?php echo CHtml::link('报名页', $competition->getUrl('registration')); ?>查看）（电子版和打印的纸质版均可）及<?php endif; ?>选手本人身份证件（身份证、户口簿、护照、台胞证、回乡证）原件。
  </li>
  <li>
    无法支付报名费的非中国大陆地区选手，需在签到时补缴报名费。
  </li>
  <li>
    签到时，选手将会领取<b>参赛证</b><?php if ($competition->entry_ticket): ?>和<b>入场凭证</b><?php endif; ?>，请妥善保管。
  </li>
  <li>
    选手在上台参赛的时候需佩戴参赛证，没有参赛证的选手将失去参赛资格。<?php if ($competition->name_card_fee > 0): ?>若参赛证遗失，可以凭报名二维码及选手本人身份证件在咨询台补办，补办费用为<b><?php echo $competition->name_card_fee; ?></b>元。<?php endif; ?>
  </li>
  <?php if ($competition->entry_ticket): ?>
  <li>
    <b>比赛所有区域需佩戴比赛入场凭证方可进入</b>，请所有选手、比赛陪同与观众在完成签到后妥善保管自己的入场凭证。
  </li>
  <li>
    入场凭证为选手进入比赛各功能区域的单独标识，一人一证切勿转让，转让带来后果由本人承担，如不能参加比赛等。
  </li>
  <?php endif; ?>
  <li>
    <?php if ($competition->guest_limit): ?>本次比赛有陪同人员限制，费用详见本页下面。<?php endif; ?>陪同人员仅可停留在观众区，不能进入选手候赛区和比赛区。
  </li>
</ol>
<h3>三、关于比赛</h3>
<ol>
  <?php if ($competition->isWCACompetition()): ?>
  <li>
    选手须确保所使用的魔方符合<a href="https://www.worldcubeassociation.org/regulations/translations/chinese/#article-3-puzzles" target="_blank">WCA规则要求</a>，并做好参赛准备，否则将被取消参与该项目的资格。
  </li>
  <?php endif ;?>
  <?php if (strpos(implode('', array_keys($competition->associatedEvents)), 'bf') !== false): ?>
  <li>
    请参加盲拧项目的选手<b>自备眼罩</b>，作为盲拧项目进行时选手的佩戴遮挡物。主办方不提供盲拧的眼罩借用。
  </li>
  <?php if ($competition->hasSchedule('333bfcheck')): ?>
  <li>
    三阶盲拧项目要求选手统一检录，然后对选手进行分组，错过项目检录即视为放弃比赛。
  </li>
  <?php endif ;?>
  <?php if ($competition->hasSchedule('444bfcheck')): ?>
  <li>
    四阶盲拧项目要求选手统一检录，然后对选手进行分组，错过项目检录即视为放弃比赛。
  </li>
  <?php endif ;?>
  <?php if ($competition->hasSchedule('555bfcheck')): ?>
  <li>
    五阶盲拧项目要求选手统一检录，然后对选手进行分组，错过项目检录即视为放弃比赛。
  </li>
  <?php endif ;?>
  <?php endif ;?>
  <li>
    粗饼网公布的赛程为比赛主办预计赛程，报名截止后可能会依据报名人数调整晋级人数或者赛程，同时比赛日也可能根据现场情况对赛程进行调整，请在比赛日注意现场通知以免耽误您的参赛。
  </li>
  <?php if (isset($competition->associatedEvents['clock']) && $competition->isWCACompetition()) :?>
  <li>
    WCA代表可能会判定下列魔表违规并禁止在比赛中使用：对于磨损过于严重的Rubik's魔表或者制造质量一般的国产魔表，如果控制齿轮转动的按钮（pin、针）不能维持凸起的状态，即依靠重力针会松动自动下落或者部分按钮的功能失效无法控制其齿轮转动的；对于改造的魔表，如果用于固定魔表两面或侧面的胶带或贴纸粘贴不对称导致可以通过胶带或贴纸观察出12点钟方向的。
  </li>
  <?php endif; ?>
  <li>
    <b>还原时限</b>：若选手的单次最终还原时间达到或者超过该时限，当次还原将记为DNF，裁判将中止该次复原。
  </li>
  <li>
    <b>及格线</b>：对于5次还原项目（如四阶速拧等），选手的前2次还原中，需至少有1次的最终成绩低于及格线成绩，否则无后3次还原机会；对于3次还原项目（如六阶速拧和七阶速拧），选手第1次还原的最终成绩需低于及格线成绩，否则无后2次还原机会。
  </li>
  <li>
    选手在赛场内应自律并尊重比赛，不大声喧哗，不破坏公物，不影响他人。
  </li>
  <li>
    进入赛场后请自觉将手机静音，并关闭相机等设备的闪光灯，文明观赛。
  </li>
  <li>
    未经组委会许可的任何组织和个人，不可在赛场进行资料派发、物料张贴及视频直播等宣传行为。
  </li>
  <li>
    任何政治符号（比如说国旗）不允许出现在赛场上。感谢所有选手、参赛陪同与观众能够配合。
  </li>
</ol>
<h3>四、比赛颁奖</h3>
<ol>
  <li>
    比赛将为各项目前<b><?php echo $competition->podiums_num ;?></b>名颁奖。
  </li>
  <?php if ($competition->attend_ceremory): ?>
  <li>
    赛事颁奖需本人出席方可领奖（奖状、奖品、奖牌、奖金），他人不可代领。未出席颁奖仪式的获奖者视为放弃领奖。
  </li>
  <?php endif; ?>
  <?php if ($competition->podiums_greater_china): ?>
  <li>
    本次比赛将同时为中华组冠亚季军（中国大陆及港澳台地区项目前三）颁奖。
  </li>
  <?php endif; ?>
  <?php $groups = $competition->getUnofficialGroups(); ?>
  <?php if ($competition->podiumsEvents !== [] && $groups !== []): ?>
  <li>
    <p>
      本次比赛<?php echo implode('、', array_map(function($event){
    return Events::getFullEventName($event);
  }, $competition->podiumsEvents)); ?>项目开设<?php echo implode('、', $groups); ?>共<?php echo count($groups); ?>个组别，以上各组别均以初赛成绩为统计依据。
    </p>
    <ol>
      <!--      U group-->
      <?php $lastAge = 0; ?>
      <?php foreach (Competition::getPodiumAges() as $age): ?>
      <?php if ($competition->{'podiums_u' . $age}): ?>
      <li>
        <b>U<?php echo $age; ?>：</b>为<?php if ($lastAge): echo $lastAge; ?>岁（含）至<?php echo $age; ?>岁（不含）<?php else: echo $age; ?>岁（不含）以下<?php endif; ?>选手，即在<?php
        if ($lastAge): echo date('Y年m月d日', $competition->getYearsAgosDate($age, 86400)), '至', date('Y年m月d日', $competition->getYearsAgosDate($lastAge));
        else: echo date('Y年m月d日', $competition->getYearsAgosDate($age, 86400)), '及之后'; endif; ?>出生的选手。
      </li>
      <?php $lastAge = $age; ?>
      <?php endif; ?>
      <?php endforeach; ?>


      <!-- O group -->

      <?php
      $oldAgeGroups = [];
      $lastOAge = 0;

      foreach ($competition->getPodiumOldAges('asc') as $age) {
        if ($lastOAge) {
          $oldAgeGroups[] = sprintf(
            '<li><b>O%d：</b>为%d岁（含）至%d岁（不含）选手，即在%s至%s出生的选手。</li>',
            $age, $lastOAge, $age,
            date('Y年m月d日', $competition->getYearsAgosDate($age, 86400)),
            date('Y年m月d日', $competition->getYearsAgosDate($lastOAge))
          );
        } else {
          $oldAgeGroups[] = sprintf(
            '<li><b>O%d：</b>为%d岁（不含）以上选手，即在%s之前出生的选手。</li>',
            $age, $age, date('Y年m月d日', $competition->getYearsAgosDate($age, 86400))
          );
        }
        $lastOAge = $age;
      }
      foreach (array_reverse($oldAgeGroups) as $group) {
        echo $group;
      }
      ?>

      <?php if ($competition->podiums_children && $lastAge === 0): ?>
      <li>
        <b>少儿组</b>：12岁（不含）以下选手。
      </li>
      <?php endif; ?>
      <?php if ($competition->podiums_females): ?>
      <li>
        <b>女子组</b>：限定为女性选手。
      </li>
      <?php endif; ?>
      <?php if ($competition->podiums_new_comers): ?>
      <li>
        <b>新人组</b>：首次参加WCA比赛的选手。
      </li>
      <?php endif; ?>
    </ol>
  <?php endif; ?>
</li>
</ol>
<h3>五、其他</h3>
<?php echo $competition->getAttributeValue('regulations'); ?>
