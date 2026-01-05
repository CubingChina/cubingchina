<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
  <td style="<?php echo $style; ?>">
    <p><strong>亲爱的管理员：</strong></p>
    <p><?php echo $user->name_zh; ?>已完成【<?php echo $competition->name_zh; ?>】的内容填写，请审核并进行预公示。</p>
    <p>点击查看：<?php echo CHtml::link($competition->name_zh, $url); ?></p>
  </td>
<tr>

