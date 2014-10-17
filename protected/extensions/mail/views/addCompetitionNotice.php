<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
	<td style="<?php echo $style; ?>">
		<p><strong>亲爱的管理员：</strong></p>
		<p><?php echo $user->name_zh; ?>刚刚创建了一场比赛，点击查看：</p>
		<p><?php echo CHtml::link($competition->name_zh, $url); ?></p>
	</td>
<tr>
</tr>
	<td style="<?php echo $style; ?>">
		<p>I don't think you need English content.</p>
	</td>
</tr>