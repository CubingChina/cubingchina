<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
  <td style="<?php echo $style; ?>">
    <p><strong>亲爱的<?php echo $user->name_zh ?: $user->name; ?>：</strong></p>
    <p>
      我们十分荣幸地通知您，您已成功报名<?php echo $competition->name_zh; ?>。<br>
      您参加了<span style="color:red"><?php echo $events['cn']; ?></span>。<br>
      同时，请您牢记下述注意事项，以免带来不便。<br>
    </p>
    <ol>
      <li>请时刻留意比赛页面信息（<?php echo CHtml::link($url, $url, ['target'=>'_blank']); ?>），以免错过重要通知。</li>
      <li>
        签到时请出示二维码及相关证件。您可以随时在比赛页面的报名信息里找到二维码。<br>
        <?php echo CHtml::image($qrCodeUrl); ?>
      </li>
      <li>请所有参赛选手必须熟知WCA规则，详见<a href="https://www.worldcubeassociation.org/regulations/translations/chinese/" target="_blank">https://www.worldcubeassociation.org/regulations/translations/chinese/</a>。</li>
      <li>请妥善保管参赛证，如若丢失，将失去比赛资格。</li>
      <li>若有故不能前来，请于比赛前发邮件联系主办方告知。</li>
      <li>所有项目不得晚于指定时间检录，否则视为放弃该项目比赛资格。</li>
      <li>还原时限：指选手的单次还原超过该时限，WCA代表和主裁判有权利停止当次比赛并记DNF。及格线：指选手五次还原的前二次须至少有一次进入及格线，否则无后三次还原机会（对于六阶、七阶，第一次还原为及格线）。</li>
      <?php if ($registration->hasRegisteredOneOf(['333bf', '333mbf', '444bf', '555bf'])): ?>
      <li>参加盲拧项目自备眼罩，否则将被取消该项目参赛资格。</li>
      <?php endif; ?>
      <?php if ($registration->hasRegisteredOneOf(['333mbf', '444bf', '555bf'])): ?>
      <li>请参加高盲、多盲项目的选手于指定时间内上交比赛用魔方，否则视为放弃该项目本次比赛资格。</li>
      <?php endif; ?>
      <?php if ($registration->hasRegisteredOneOf(['444bf', '555bf'])): ?>
      <li>高盲累计时限：在一轮中，N次(N≤3)还原的累计时间不能超过规定的时限。当选手累计时间到达时限时，裁判可以直接叫停选手的复原，本次复原将被记为DNF，之后复原将被记为DNS。对于成绩为DNF的复原，裁判也将记录所用时间并计入累计</li>
      <?php endif; ?>
      <?php if ($registration->hasRegistered('clock')): ?>
      <li>对于磨损过于严重及低质量魔表，若控制齿轮转动的针不能维持凸起的状态，即重力下针会松动下落或者无法控制其齿轮转动。主办方可能禁止其在比赛中使用。</li>
      <?php endif; ?>
      <li><?php echo $competition->organizer[0]->user->name_zh; ?>保留最终解释权。</li>
      若有任何疑问，请通过邮件联系我们。<br>
      顺颂春祺！<br>
      <?php echo $competition->organizer[0]->user->name_zh; ?><br>
      <?php echo $competition->organizer[0]->user->email; ?>
    </p>
    <p>本邮件由系统自动发出，请勿回复，如有疑问，请联系<?php echo Yii::app()->params->adminEmail; ?>。</p>
  </td>
<tr>
</tr>
  <td style="<?php echo $style; ?>">
    <p><strong>Dear <?php echo $user->name; ?>：</strong></p>
    <p>
      We are very privileged to inform you that, you succeeded in registering for <?php echo $competition->name; ?>.<br>
      You participate in <span style="color:red"><?php echo $events['en']; ?></span>. <br>
      Meanwhile, you must deeply remember the followings to avoid any inconveniences.<br>
      <?php $i = 0; ?>
      <li>Keep close attention to the competition information through our website (<?php echo CHtml::link($url, $url, array('target'=>'_blank')); ?>), to avoid missing any important notices.</li>
      <li>Please show staffs the QR code and the corresponding ID credentials for check-in. You can find it in your registration page at all time.</li>
      <?php echo CHtml::image($qrCodeUrl); ?><br>
      <li>All competitors must be familiar with the WCA regulations. Regulations can be found at <a href="https://www.worldcubeassociation.org/regulations/" target="_blank">https://www.worldcubeassociation.org/regulations/</a>.</li>
      <li>Competitors are required to carry their competitor ID with them.</li>
      <li>Please inform us by email before the competition if you will not be able to participate so we can cancel your registration.</li>
      <li>Please pay attention to the schedule and be on time for your events. Competitors showing up late to events may be disqualified.</li>
      <li>"Time limit" means, if you exceeds the time limit, your current attempt will be stopped and recorded as DNF. "Cut-off" means, you are allowed to finish all five attempts if at least one of your first two attempts fits in the cut-off, otherwise the remaining three attempts will be cancelled. (The first attempt has to be below the cut-off for 6x6, 7x7 and 3x3 with feet.)</li>
      <?php if ($registration->hasRegisteredOneOf(['333bf', '333mbf', '444bf', '555bf'])): ?>
      <li>Competitors participating in all blindfolded events must provide their own blindfold.</li>
      <?php endif; ?>
      <?php if ($registration->hasRegisteredOneOf(['333mbf', '444bf', '555bf'])): ?>
      <li>For 4x4 blindfolded, 5x5 blindfolded and 3x3 multiple blindfolded events, cubes must be provided for scrambling when requested by the organizers.</li>
      <?php endif; ?>
      <?php if ($registration->hasRegisteredOneOf(['444bf', '555bf'])): ?>
      <li>For 4x4 blindfolded and 5x5 blindfolded, “cumulative time limit” means that the total solving time of N attempts (N≤3) mustn’t exceeds the given time limit. If your total time exceeds the time limit, your current attempt will be stopped and recorded as DNF and any remaining attempts will be recorded as DNS. Attempt time for incomplete solves will be still recorded and added to the cumulative time.</li>
      <?php endif; ?>
      <?php if ($registration->hasRegistered('clock')): ?>
      <li>For the Clock event, clocks will be disqualified if the four pins can't stay upright, such that the pins fall down if the clock is held horizontal or the pins fail to control the rotation of gears.</li>
      <?php endif; ?>
      <li><?php echo $competition->organizer[0]->user->name; ?> reserves the final explanation right.</li>
      If you have any questions, feel free to ask us by email.<br>
      Best regards!<br>
      <?php echo $competition->organizer[0]->user->name; ?><br>
      <?php echo $competition->organizer[0]->user->email; ?>
    </p>
    <p>This is a system-generated Email. Please do not reply to this Email. If you have any question, you can contact <?php echo Yii::app()->params->adminEmail; ?>.</p>
  </td>
</tr>
