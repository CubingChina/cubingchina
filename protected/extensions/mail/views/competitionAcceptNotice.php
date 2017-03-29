<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
  <td style="<?php echo $style; ?>">
    <p><strong>亲爱的<?php echo $user->name_zh ?: $user->name; ?>：</strong></p>
    <p>你提交的【<?php echo $competition->name_zh; ?>】申请已被管理员审核通过。请点击如下链接进一步编辑各信息之后回复本邮件公示：</p>
    <p><?php echo CHtml::link($competition->name_zh, $url); ?></p>
    <p><i>回复邮件请务必选择“回复全部”或者“回复所有人”。</i></p>
  </td>
<tr>
