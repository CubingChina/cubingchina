<?php

class PayController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ORGANIZER,
				),
				'actions'=>[
					'index',
					'ticket',
					'exportTicket',
					'toggleTicket'
				],
			),
			array(
				'allow',
				'roles'=>[
					'permission'=>'caqa_member'
				],
				'actions'=>[
					'index'
				]
			),
			array(
				'allow',
				'roles'=>[
					'permission'=>'caqa'
				],
				'actions'=>[
					'index',
					'ticket',
					'exportTicket',
					'toggleTicket'
				]
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
		$model->type = Pay::TYPE_REGISTRATION;
		$model->attributes = $this->aRequest('Pay');
		if ($this->user->isOrganizer()) {
			$model->type = Pay::TYPE_REGISTRATION;
			if ($model->type_id == null) {
				$model->type_id = 0;
			}
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id]) && !Yii::app()->user->checkPermission('caqa')) {
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
		$this->render('bill', [
			'model'=>$model,
		]);
	}

	public function actionTicket() {
		$model = new UserTicket('search');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('UserTicket');
		// 默认只看已支付
		if ($model->status === null) {
			$model->status = UserTicket::STATUS_PAID;
		}

		// 主办方只能查看自己比赛的入场券
		if ($this->user->isOrganizer() && $model->competition_id && !Yii::app()->user->checkPermission('caqa')) {
			$competition = Competition::model()->findByPk($model->competition_id);
			if ($competition && !isset($competition->organizers[$this->user->id])) {
				Yii::app()->user->setFlash('danger', '权限不足！');
				$this->redirect(['/board/pay/ticket']);
			}
		}

		$this->render('ticket', [
			'model'=>$model,
		]);
	}

	public function actionExportTicket() {
		$model = new UserTicket('search');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('UserTicket');

		// 主办方只能查看自己比赛的入场券
		if ($this->user->isOrganizer() && $model->competition_id && !Yii::app()->user->checkPermission('caqa')) {
			$competition = Competition::model()->findByPk($model->competition_id);
			if ($competition && !isset($competition->organizers[$this->user->id])) {
				Yii::app()->user->setFlash('danger', '权限不足！');
				$this->redirect(['/board/pay/ticket']);
			}
		}

		// 获取所有数据（不分页）
		$dataProvider = $model->search();
		$dataProvider->pagination = false;
		$tickets = $dataProvider->getData();

		// 设置文件名
		$competition = null;
		if ($model->competition_id) {
			$competition = Competition::model()->findByPk($model->competition_id);
		}
		$filename = '入场券购买记录';
		if ($competition !== null) {
			$filename .= '_' . $competition->name_zh;
		}
		$filename .= '_' . date('YmdHis') . '.csv';

		// 输出CSV
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		// 输出UTF-8 BOM以便Excel正确显示中文
		echo "\xEF\xBB\xBF";

		// 打开输出流
		$output = fopen('php://output', 'w');

		// 输出表头
		$headers = [
			'ID',
			'比赛',
			'入场券',
			'购买人',
			'入场人',
			'证件类型',
			'证件号码',
			'支付金额',
			'支付时间',
			'状态',
			'签到状态',
			'签到时间',
			'创建时间',
		];
		fputcsv($output, $headers);

		// 输出数据
		foreach ($tickets as $ticket) {
			$row = [
				$ticket->id,
				$ticket->ticket && $ticket->ticket->competition ? $ticket->ticket->competition->name_zh : '',
				$ticket->ticket ? $ticket->ticket->name_zh : '',
				$ticket->user ? $ticket->user->getCompetitionName() : '',
				$ticket->name,
				$ticket->getPassportTypeText(),
				$ticket->passport_number,
				$ticket->paid_amount ? number_format($ticket->paid_amount / 100, 2, '.', '') : '',
				$ticket->paid_time ? date('Y-m-d H:i:s', $ticket->paid_time) : '',
				$ticket->getStatusText(),
				$ticket->signed_in ? '已签到' : '未签到',
				$ticket->signed_date ? date('Y-m-d H:i:s', $ticket->signed_date) : '',
				$ticket->create_time ? date('Y-m-d H:i:s', $ticket->create_time) : '',
			];
			fputcsv($output, $row);
		}

		fclose($output);
		Yii::app()->end();
	}

	public function actionToggleTicket() {
		$id = $this->iRequest('id');
		$attribute = $this->sRequest('attribute');
		$model = UserTicket::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'Not found');
		}
		$competition = $model->ticket ? $model->ticket->competition : null;
		if ($competition === null) {
			throw new CHttpException(404, 'Not found');
		}
		// 主办方只能操作自己比赛的入场券
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id]) && !Yii::app()->user->checkPermission('caqa')) {
			throw new CHttpException(401, 'Unauthorized');
		}
		if ($attribute == 'signed_in') {
			$model->signed_in = 1 - $model->signed_in;
			if ($model->signed_in) {
				$model->signed_date = time();
				$auth = ScanAuth::getCompetitionAuth($competition);
				$model->signed_scan_code = $auth ? $auth->code : '';
			} else {
				$model->signed_date = 0;
				$model->signed_scan_code = '';
			}
		} else {
			$model->$attribute = 1 - $model->$attribute;
		}
		$model->save();
		$this->ajaxOk(array(
			'value'=>$model->$attribute,
		));
	}
}
