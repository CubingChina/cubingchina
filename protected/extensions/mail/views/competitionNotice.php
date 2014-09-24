<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
	<td style="<?php echo $style; ?>">
		<p><strong>亲爱的<i><?php echo $competition->name_zh; ?></i>参赛者：</strong></p>
		<?php echo $content; ?>
		<p>本邮件由系统代发，如有疑问，请联系主办方：<br><?php echo implode('<br>', $organizers); ?>。</p>
	</td>
<tr>
<?php if (trim(strip_tags($englishContent, '<img>')) != ''): ?>
</tr>
	<td style="<?php echo $style; ?>">
		<p>Dear competitior in <i><?php echo $competition->name; ?></i>,</p>
		<?php echo $englishContent; ?>
		<p>This is a system-sent Email. If you have any question, you can contact<br><?php echo implode('<br>', $organizers); ?>.</p>
	</td>
</tr>
<?php endif; ?>