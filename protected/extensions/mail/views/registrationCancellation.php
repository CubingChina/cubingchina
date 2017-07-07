<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
  <td style="<?php echo $style; ?>">
    <p><strong>亲爱的<?php echo $user->name_zh ?: $user->name; ?>：</strong></p>
    <p>
      您报名的<b><?php echo $competition->name_zh; ?></b>已成功退赛。<br>
      <?php if ($registration->getRefundFee() > 0): ?>
      您所退回的费用将通过付款时选择的支付方式退回。<br>
      <?php endif; ?>
      <br>
      如有疑问，请联系下列比赛主办方，勿回复本邮件。<br>
      <?php foreach ($competition->organizer as $organizer): ?>
      <?php echo $organizer->user->name_zh; ?>：
      <?php echo $organizer->user->email; ?><br>
      <?php endforeach; ?>
    </p>
  </td>
<tr>
</tr>
  <td style="<?php echo $style; ?>">
    <p><strong>Dear <?php echo $user->name; ?>,</strong></p>
    <p>
      Your registration for <b><?php echo $competition->name; ?></b> has been cancelled.<br>
      <?php if ($registration->getRefundFee() > 0): ?>
      The refund will be made via the payment method which you have used at the registration.<br>
      <?php endif; ?>
      <br>
      If you have any questions, please contact the organizer. Do not reply to this Email<br>
      <?php foreach ($competition->organizer as $organizer): ?>
      <?php echo $organizer->user->name; ?>:
      <?php echo $organizer->user->email; ?><br>
      <?php endforeach; ?>
    </p>
  </td>
</tr>
