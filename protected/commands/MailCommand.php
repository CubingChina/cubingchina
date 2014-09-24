<?php 
class MailCommand extends CConsoleCommand {
	public function actionIndex() {
		$mails = Mail::model()->findAllByAttributes(array(
			'sent'=>0,
		), array(
			'order'=>'update_time ASC',
			'limit'=>20,
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
			//发5封后休息一会
			if ($key % 5 == 4) {
				sleep(5);
			}
		}
	}
}
