<?php

class MailCommand extends CConsoleCommand {
	public function actionIndex() {
		$condition = '';
		$count = Mail::model()->countByAttributes(array(
			'sent'=>1,
		), array(
			'condition'=>'update_time>' . strtotime('today'),
		));
		if ($count >= 200) {
			$condition = '0';
		} elseif ($count >= 185) {
			$condition = 'subject LIKE "%注册%" OR subject LIKE "%密码%" DESC';
		}
		$mails = Mail::model()->findAllByAttributes(array(
			'sent'=>0,
		), array(
			'condition'=>$condition,
			'order'=>'subject LIKE "%注册%" DESC , subject LIKE "%密码%" DESC , subject LIKE "%报名%" DESC , update_time ASC',
			'limit'=>30,
		));
		$mailer = Yii::app()->mailer;
		foreach ($mails as $key=>$mail) {
			$result = $mailer->send($mail);
			$mail->update_time = time();
			if ($result == true) {
				$mail->sent_time = time();
				$mail->sent = 1;
			} elseif ($mail->update_time - $mail->add_time > 86400) {
				$mail->sent = 2;
			}
			$mail->save();
			sleep(1);
		}
	}
}
