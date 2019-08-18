<?php
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class AdminController extends Controller {
	public $layout = '/layouts/main';
	public $alerts = array();
	protected $minIEVersion = '9.0';

	public function beforeAction($action) {
		if (parent::beforeAction($action)) {
			$criteria = new CDbCriteria();
			$criteria->with = array(
				'organizer'=>array(
					'together'=>true,
				),
			);
			$criteria->compare('t.id', '>370');
			$criteria->compare('t.status', Competition::STATUS_SHOW);
			$criteria->compare('organizer.organizer_id', Yii::app()->user->id);
			$competitions = Competition::model()->findAll($criteria);
			foreach ($competitions as $competition) {
				if (!$competition->isScheduleFinished()) {
					$this->alerts[] = array(
						'url'=>array('/board/competition/edit', 'id'=>$competition->id),
						'label'=>sprintf('"%s"赛程不完整', $competition->name_zh),
					);
				}
			}
			Yii::app()->language = 'zh_cn';
			if (strpos($action->id, 'edit') !== false) {
				$this->setReferrer();
			}
			return true;
		}
		return false;
	}

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('@'),
				'roles'=>array(
					'role'=>User::ROLE_CHECKED,
				),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	protected function exportToExcel($excel, $path = 'php://output', $filename = 'CubingChina', $xlsx = true, $preCalculateFormulas = false) {
		$download = $path === 'php://output';
		$excel->setActiveSheetIndex(0);
		Yii::app()->controller->setIsAjaxRequest(true);
		if ($xlsx) {
			$writer = new Xlsx($excel);
		} else {
			$writer = new Xls($excel);
		}
		if ($download) {
			if ($xlsx) {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
			} else {
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
			}
		}
		$writer->setPreCalculateFormulas($preCalculateFormulas);
		$writer->save($path);
		if ($download) {
			exit;
		}
	}
}
