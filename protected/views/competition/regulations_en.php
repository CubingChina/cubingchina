<h3>1. About the registration</h3>
<ol>
  <?php if ($competition->series): ?>
  <?php $otherCompetitions = implode(', ', array_map(function($series) {
    $competition = $series->competition;
    return CHtml::link($competition->getAttributeValue('name'), $competition->getUrl());
  }, array_filter($competition->series->list, function($series) use ($competition) {
    return $series->competition_id != $competition->id;
  }))); ?>
  <li>
    Register for a Series:
    <ol>
      <li>Competitors can only register for one competition in a Series.</li>
      <li>Successfully registered competitors can not register for <?php echo $otherCompetitions; ?>.</li>
      <li>Registered competitors can only register for <?php echo $otherCompetitions; ?> if they cancel their registration for this competition.</li>
    </ol>
  </li>
  <?php endif; ?>
  <li>
    Registration must be completed during the registration period (from <?php echo date('Y-m-d H:i:s', $competition->reg_start), ' to ', date('Y-m-d日 H:i:s', $competition->reg_end); ?>). Late registrations will not be accepted. Registration must be done online, competitors cannot register at the competition.
  </li>
  <li>
    Registration must be done through the <?php echo CHtml::link('competition website', $competition->getUrl('registration')); ?> on <a href="/">CubingChina</a>.
  </li>
  <?php if ($competition->hasSecondStage): ?>
  <?php $phase = 1; ?>
  <?php $ordinals = ['', '', 'st', 'nd', 'rd']; ?>
  <li>
    There are <b><?php echo 1 + $competition->hasSecondStage + $competition->hasThirdStage; ?></b> registration periods. The <?php
    echo $phase++, $ordinals[$phase]; ?> registration period is from <?php echo $competition->reg_start ? date('Y-m-d H:i:s', $competition->reg_start) : 'now'; ?> to <?php echo date('Y-m-d H:i:s', $competition->second_stage_date - 1); ?><?php if ($competition->hasThirdStage): ?>. The <?php
    echo $phase++, $ordinals[$phase]; ?> registration period is from <?php echo date('Y-m-d H:i:s', $competition->second_stage_date); ?> to <?php echo date('Y-m-d H:i:s', $competition->third_stage_date - 1); ?><?php endif; ?>. The <?php
    echo $phase++, $ordinals[$phase]; ?> registration period is from <?php echo date('Y-m-d H:i:s', $competition->hasThirdStage ? $competition->third_stage_date : $competition->second_stage_date); ?> to <?php echo date('Y-m-d H:i:s', $competition->reg_end); ?>. Registration fees are increased after the first registration period.
  </li>
  <?php endif; ?>
  <li>
    Registration fees (in Chinese yuan) consist of a base fee and <?php echo CHtml::link('event fees', $competition->getUrl('detail', ['#'=>'fees'])); ?>. <?php if ($competition->hasSecondStage): ?>The base fee during the 1st registration period is ¥<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_FIRST) ;?>. The base fee during the 2nd registration period is ¥<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_SECOND) ;?>.<?php
    if ($competition->hasThirdStage): ?> The base fee during the 3rd registration period is ¥<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY, Competition::STAGE_THIRD) ;?>.<?php endif; ?><?php else: ?>The base fee is ¥<?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY) ;?>.<?php endif; ?>
  </li>
  <li>
    Chinese mainland competitors must pay online to complete their registration. Unpaid registrations will not be accepted.<?php if ($competition->paypal_link): ?> International competitors may pay by PayPal.<?php endif; ?> International competitors that cannot pay online should contact the delegate or organizer by email.
  </li>
  <?php if ($competition->has_qualifying_time): ?>
  <li>
    <b>Qualifying Times</b>
    <ol>
      <li>At the time of registration, the competitors can only choose the events passed the qualifying times. To register the competition, one needs at least one event that meets the qualifying time.</li>
      <li>In order to register for any events you like, you must meet the qualifying times before <?php echo  date('Y-m-d H:i', $competition->qualifying_end_time); ?>. Qualifying times can be found <?php echo CHtml::link('here', $competition->getUrl('detail', ['#'=>'events'])) ?>.</li>
      <li>All qualification results are based on <a href="https://www.worldcubeassociation.org/results/" target="_blank">WCA official results</a>.</li>
    </ol>
  </li>
  <?php endif; ?>
  <?php if ($competition->cancellation_end_time > 0) :?>
  <li>
    Registration can be canceled through the competition page on CubingChina before the cancelation end time which is <?php echo date('Y-m-d H:i:s', $competition->cancellation_end_time); ?>.<?php if ($competition->refund_type !== Competition::REFUND_TYPE_NONE): ?> Canceled registrations can receive a <?php echo $competition->refund_type; ?>% refund of registration fees.<?php endif; ?> Competitors that have canceled their registration cannot register again for this competition.
  </li>
  <?php endif; ?>
  <?php if ($competition->allow_change_event) :?>
  <li>
    Registered competitors can add events to their registration through the <?php echo CHtml::link('registration page', $competition->getUrl('registration')); ?> on cubingchina before <?php echo date('Y-m-d H:i:s', $competition->reg_end); ?>. Event registration fees must be paid for added events.
  </li>
  <?php endif; ?>
  <li>
    Registered events cannot be exchanged for other events (for example, a competitor registered for 4x4 speedsolve cannot change their registration to 2x2 speedsolve). Registrations cannot be exchanged among different people.
  </li>
  <li>
    No refunds for payments will be made<?php if ($competition->cancellation_end_time > 0 && $competition->refund_type !== Competition::REFUND_TYPE_NONE): ?>, aside from refunded fees for properly canceled registrations<?php endif; ?>.
  </li>
  <li>
    Competitors under 18 years of age must have parental permission before registering.
  </li>
  <?php if ($competition->person_num > 0): ?>
  <li>
    There is a competitor limit of <?php echo $competition->person_num; ?> competitors.
  </li>
  <?php endif; ?>
</ol>
<h3>2. Check in at the competition venue</h3>
<ol>
  <li>
    Competitors must check in during the specified check-in period. Competitors much check in in person, and cannot have someone else check in for them.
  </li>
  <li>
    Competitors should follow posted signs for check in. Non-competitors should not enter the check in area.
  </li>
  <li>
    <?php if ($competition->show_qrcode): ?>Competitors should show the registration QR code (printed or electronic) which is found on their CubingChina <?php echo CHtml::link('registration page', $competition->getUrl('registration')); ?> or emailed after registration. <?php endif; ?>Competitors should present official identification during check in.
  </li>
  <li>
    International competitors who did not pay registration fees online should pay in cash during check in.
  </li>
  <li>
    During check in competitors will receive a <b>competition ID</b><?php if ($competition->entry_ticket): ?> and a <b>entry permit</b><?php endif; ?>. Please keep the <?php echo $competition->entry_ticket ? 'them' : 'it'; ?> with you during the competition.
  </li>
  <li>
    Competitors must have their competition ID with them when they compete.<?php if ($competition->name_card_fee > 0): ?> Replacement competition ID can be obtained for a fee by presenting the competitor’s official ID and registration QR code.<?php endif; ?>
  </li>
  <?php if ($competition->entry_ticket): ?>
  <li>
    An entry permit is needed to enter the venue and any functional areas within. All competitors and guests are responsible for safely keeping their entry permit.
  </li>
  <li>
    The entry permit for each person is unique; Please DO NOT resell your permit to others. Competitors and guests are responsible for keeping their permits.
  </li>
  <?php endif; ?>
  <li>
    <?php if ($competition->guest_limit): ?>There's a limit for guests. Please see the bottom of this page for details. <?php endif; ?>Accompanying spectators may enter the venue, but must not enter the competition area.
  </li>
</ol>
<h3>3. About the competition</h3>
<ol>
  <?php if ($competition->isWCACompetition()): ?>
  <li>
    Competitors must confirm that their cube is acceptable based on <a href="https://www.worldcubeassociation.org/regulations/#article-3-puzzles" target="_blank">WCA regulations</a>, otherwise results may not be accepted.
  </li>
  <?php endif ;?>
  <?php if (strpos(implode('', array_keys($competition->associatedEvents)), 'bf') !== false): ?>
  <li>
    Competitors in blindfolded events must provide their own blindfold.
  </li>
  <?php endif; ?>
  <li>
    The competition schedule shown on the competition website is an initial estimate. At the end of registration, the organizer may adjust the schedule, cutoffs, or time limits, based on the number of registered competitors. The schedule may be adjusted during the course of the competition. Competitors should be aware of notices made during the competition to avoid missing events.
  </li>
  <?php if (isset($competition->associatedEvents['clock']) && $competition->isWCACompetition()) :?>
  <li>
    The WCA delegate will determine if clock puzzles are acceptable to use in competition. Examples of unacceptable clock puzzles: puzzles that are damaged; puzzles with pins that do not correctly control clock position; puzzles with pins that cannot hold their position; puzzles with modifications that make the solved state apparent from the exterior of the puzzle.
  </li>
  <?php endif; ?>
  <li>
    <b>Time limits</b>: If a competitor’s solve time exceeds the time limit for an event, the solve will be recorded as DNF and the judge may stop the competitor from continuing the solve.
  </li>
  <li>
    <b>Cutoffs</b>: In events recorded as an average of five solves, the first two solves must have at least one solve below the cutoff for the event in order to do the remaining three solves of the round. In events recorded as a mean of three solves, the first solve must be below the cutoff for the event in order to do the remaining two solves.
  </li>
  <li>
    Competitors should respect the competition venue and other competitors. Competitors should not make excess noise, damage facilities, or disturb other competitors.
  </li>
  <li>
    During the competition please silence phones and disable flash on cameras.
  </li>
  <li>
    Distribution, advertising, or broadcast without permission of competition organizers is forbidden.
  </li>
  <li>
    Political symbols (such as national flags) are forbidden in the competition venue. We appreciate competitors and spectators maintaining a peaceful competition atmosphere.
  </li>
</ol>
<h3>4. Competition awards</h3>
<ol>
  <li>
    The top <b><?php echo $competition->podiums_num ;?></b> competitors of each event will be awarded.
  </li>
  <?php if ($competition->attend_ceremory): ?>
  <li>
    Competitors must accept any certificates, awards, or prizes in person. Competitors that do not appear in person will forfeit any prizes.
  </li>
  <?php endif; ?>
  <?php if ($competition->podiums_greater_china): ?>
  <li>
    The greater China group will be awarded too.
  </li>
  <?php endif; ?>
  <?php $groups = $competition->getUnofficialGroups(); ?>
  <?php if ($competition->podiumsEvents !== [] && $groups !== []): ?>
  <li>
    <p>
      Events <?php echo implode(', ', array_map(function($event) {
    return Events::getFullEventName($event);
  }, array_slice($competition->podiumsEvents, 0, -1))), count($competition->podiumsEvents) > 1 ? ' and ' : '', Events::getFullEventName($competition->podiumsEvents[count($competition->podiumsEvents) - 1]); ?> include<?php if (count($competition->podiumsEvents) === 1) echo 's'; ?> the following groups <?php echo implode(', ', $groups); ?>. Awards for the above groups are based on results of the first round.
    </p>
    <ol>
      <?php $lastAge = 0; ?>
      <?php foreach (Competition::getPodiumAges() as $age): ?>
      <?php if ($competition->{'podiums_u' . $age}): ?>
      <li>
        <b>U<?php echo $age; ?>: </b>Competitors <?php
        if ($lastAge): echo 'between ', $lastAge, ' and ', $age;
        else: echo 'under ', $age; endif; ?> years old.
      </li>
      <?php $lastAge = $age; ?>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($competition->podiums_children && $lastAge === 0): ?>
      <li>
        <b>Children group</b>：Competitors under 12 years old.
      </li>
      <?php endif; ?>
      <?php if ($competition->podiums_females): ?>
      <li>
        <b>Female group</b>: Female competitors.
      </li>
      <?php endif; ?>
      <?php if ($competition->podiums_new_comers): ?>
      <li>
        <b>Newcomers group</b>: The new comer group is for competitors attending their first WCA competition.
      </li>
      <?php endif; ?>
    </ol>
  <?php endif; ?>
</li>
</ol>
<h3>5. Other</h3>
<?php echo $competition->getAttributeValue('regulations'); ?>
