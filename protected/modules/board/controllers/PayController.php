<?php

class PayController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ORGANIZER,
				),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$model = new Pay();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Pay');
		$model->type = Pay::TYPE_REGISTRATION;
		if ($this->user->isOrganizer() && $model->type_id == null) {
			$model->type_id = 0;
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/pay/index'));
		}
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionBill() {
		$model = new Pay();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Pay');
		$model->channel = Pay::CHANNEL_BALIPAY;
		$model->status = Pay::STATUS_PAID;
		if ($this->iRequest('export') == Pay::YES) {
			$data = $model->searchBill(false)->getData();
			$excel = new PHPExcel();
			$excel->getProperties()
				->setCreator(Yii::app()->params->author)
				->setLastModifiedBy(Yii::app()->params->author)
				->setTitle('对账单');
			$sheet = $excel->getSheet(0);
			$sheet->setCellValue('A1', 'ID');
			$sheet->setCellValue('B1', '类型');
			$sheet->setCellValue('C1', '名字');
			$sheet->setCellValue('D1', '金额');
			$sheet->setCellValue('E1', '手续费');
			$sheet->setCellValue('F1', '实收');
			$sheet->setCellValue('G1', '订单号');
			$sheet->setCellValue('H1', '支付宝订单号');
			$sheet->setCellValue('I1', '创建时间');
			$sheet->setCellValue('J1', '更新时间');
			$sheet->setCellValue('K1', '状态');
			$paid = $model->getTotal(Pay::STATUS_PAID, true);
			$fee = $model->getBillTotalFee();
			$sheet->setCellValue('D2', $paid);
			$sheet->setCellValue('E2', $fee);
			$sheet->setCellValue('F2', $paid - $fee);
			foreach ($data as $key=>$value) {
				$row = $key + 3;
				$sheet->setCellValue('A' . $row, $value->id);
				$sheet->setCellValue('B' . $row, $value->getTypeText());
				$sheet->setCellValue('C' . $row, $value->order_name);
				$sheet->setCellValue('D' . $row, $value->amount / 100);
				$sheet->setCellValue('E' . $row, $value->getBillFee());
				$sheet->setCellValue('F' . $row, $value->amount / 100 - $value->getBillFee());
				$sheet->setCellValue('G' . $row, $value->order_no);
				$sheet->setCellValue('H' . $row, $value->trade_no);
				$sheet->setCellValue('I' . $row, date('Y-m-d H:i:s', $value->create_time));
				$sheet->setCellValue('J' . $row, date('Y-m-d H:i:s', $value->update_time));
				$sheet->setCellValue('K' . $row, $value->getStatusText());
			}
			$this->exportToExcel($excel, 'php://output', '对账单');
			Yii::app()->end();
		}
		$this->render('bill', [
			'model'=>$model,
		]);
	}
}