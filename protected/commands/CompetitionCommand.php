<?php

class CompetitionCommand extends CConsoleCommand {
	public function actionCancel($id) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
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
					echo $registration->user->name_zh;
					foreach ($payments as $payment) {
						if ($payment->isPaid()) {
							echo ' paid: ', $payment->paid_amount, ' refund:', $payment->refund_amount, "\n";
							if ($payment->paid_amount == $payment->refund_amount) {
								continue;
							}
							$payment->refund($payment->paid_amount - $payment->refund_amount, true);
						}
					}
				} else {
					continue;
				}
			}
		}
	}

	public function actionRefund($id, $amount) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$amount = $amount * 100;
			$registrations = Registration::getRegistrations($competition);
			$sum = [
				'wechat' => 0,
				'balipay' => 0,
			];
			$refundPayments = [];
			$flag = [];
			foreach ($registrations as $registration) {
				if ($registration->isAccepted() || $registration->isCancelled()) {
					$payments = $registration->payments;
					foreach ($payments as $payment) {
						if ($payment->refund_amount > 0) {
							$flag[$registration->user_id] = 1;
							continue;
						}
						if (isset($flag[$registration->user_id])) {
							continue;
						}
						if ($payment->isPaid() && $payment->paid_amount >= $amount && !isset($refundPayments[$registration->user_id])) {
							$sum[$payment->channel] += $amount;
							$refundPayments[$registration->user_id] = $payment;
						}
					}
				}
			}
			var_dump($sum, array_sum($sum));
			if (!$this->confirm('确认退款？' . $amount)) {
				return;
			}
			foreach ($refundPayments as $payment) {
				$payment->refund($amount, true);
			}
		}
	}

	public function actionCancelTicket($id) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$ticket = Ticket::model()->findByPk($id);
		if ($ticket !== null && $this->confirm($ticket->name_zh)) {
			$tickets = UserTicket::model()->findAllByAttributes(['ticket_id'=>$ticket->id]);
			$sum = [
				'wechat'=>0,
				'balipay'=>0,
			];
			foreach ($tickets as $ticket) {
				if ($ticket->isPaid()) {
					$payment = $ticket->payment;
					if ($payment->isPaid()) {
						$sum[$payment->channel] += $payment->paid_amount - $payment->refund_amount;
					}
				}
			}
			var_dump($sum, array_sum($sum));
			if (!$this->confirm('确认取消？')) {
				return;
			}
			foreach ($tickets as $ticket) {
				if ($ticket->isPaid()) {
					$payment = $ticket->payment;
					if ($payment->isPaid()) {
						echo $ticket->name, ' paid: ', $payment->paid_amount, ' refund:', $payment->refund_amount, "\n";
						if ($payment->paid_amount == $payment->refund_amount) {
							continue;
						}
						$payment->refund($payment->paid_amount - $payment->refund_amount, true);
						$ticket->signed_in = UserTicket::YES;
						$ticket->save(false);
					}
				}
			}
		}
	}
}
