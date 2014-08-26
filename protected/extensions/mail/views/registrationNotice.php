<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<tr>
	<td style="<?php echo $style; ?>">
		<p><strong>亲爱的主办方：</strong></p>
		<p>我是粗饼君，特来告诉你，<strong><?php echo $registration->user->name_zh ?: $registration->user->name; ?></strong>刚刚报名了<strong><?php echo $registration->competition->name_zh; ?></strong>，请点击以下链接查看。</p>
		<p><a href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
		<p>如果不能点击，请复制该链接，粘帖到地址栏进行查看。</p>
		<p>本邮件由系统自动发出，请勿回复，如有疑问，请联系<?php echo Yii::app()->params->adminEmail; ?>。</p>
	</td>
<tr>
</tr>
	<td style="<?php echo $style; ?>">
		<p>I don't think you need English content.</p>
	</td>
</tr>