<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
  <td style="<?php echo $style; ?>">
    <p><strong>亲爱的<?php echo $user->name_zh ?: $user->name; ?>：</strong></p>
    <p>你提交的【<?php echo $competition->name_zh; ?>】申请已被管理员<?php echo $title; ?>，原因如下：</p>
    <p><?php echo $competition->application->reason; ?></p>
    <?php if (!$competition->isRejected()): ?>
    <p>请修改比赛申请资料，再次提交审核，点击查看：</p>
    <p><?php echo CHtml::link($competition->name_zh, $url); ?></p>
    <?php endif; ?>
  </td>
<tr>
