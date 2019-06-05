<?php
$style = 'padding-top:13px;padding-left:39px;padding-right:13px;padding-bottom:13px;text-align:left;border-bottom:1px solid #ddd';
?>
<?php if (trim(strip_tags($content, '<img>')) != ''): ?>
<tr>
    <td style="<?php echo $style; ?>">
        <p><strong>亲爱的<i><?php echo $user->name_zh ?: $user->name; ?></i>：</strong></p>
        <?php echo $content; ?>
        <p>
            本邮件由系统代发，如有疑问，请联系发信人：<?php echo $sender->name_zh ?: $sender->name; ?>（<a href="mailto:<?php echo $sender->email; ?>"><?php echo $sender->email; ?></a>）。
        </p>
    </td>
<tr>
<?php endif; ?>
<?php if (trim(strip_tags($englishContent, '<img>')) != ''): ?>
</tr>
    <td style="<?php echo $style; ?>">
        <p>Dear <i><?php echo $user->name; ?></i>,</p>
        <?php echo $englishContent; ?>
        <p>This is a system-sent Email. If you have any question, you can contact <?php echo $sender->name; ?> (<a href="mailto:<?php echo $sender->email; ?>"><?php echo $sender->email; ?></a>) .</p>
    </td>
</tr>
<?php endif; ?>
