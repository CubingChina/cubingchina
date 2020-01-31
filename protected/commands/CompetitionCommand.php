<?php

class CompetitionCommand extends CConsoleCommand {
	public function actionCancel($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$registrations = Registration::getRegistrations($competition, true);
			$sum = [
				'wechat'=>0,
				'balipay'=>0,
			];
			foreach ($registrations as $registration) {
				if ($registration->isAccepted() || $registration->isCancelled()) {
					$payments = $registration->payments;
					foreach ($payments as $payment) {
						if ($payment->isPaid()) {
							$sum[$payment->channel] += $payment->paid_amount - $payment->refund_amount;
						}
					}
				}
			}
			var_dump($sum, array_sum($sum));
			if (!$this->confirm('确认取消？')) {
				return;
			}
			foreach ($registrations as $registration) {
				if ($registration->isAccepted()){
					//改成候选就能全额退款
					$registration->status = Registration::STATUS_WAITING;
					$registration->cancel();
				} elseif ($registration->isCancelled()) {
					//已退赛的人，退回另一半
					$payments = $registration->payments;
					foreach ($payments as $payment) {
						if ($payment->isPaid()) {
							$payment->refund($payment->paid_amount - $payment->refund_amount, true);
						}
					}
				}
			}
		}
	}
}
