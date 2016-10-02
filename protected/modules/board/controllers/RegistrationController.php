<?php

class RegistrationController extends AdminController {

	const ROW_PER_CARD = 11;
	const CARD_PER_PAGE = 3;

	private $pagePerStack = 50;
	private $imageStyle = array(
		array(
			'width'=>72,
			'height'=>71,
			'offsetX'=>2,
			'offsetY'=>-13,
		),
		array(
			'width'=>72,
			'height'=>71,
			'offsetX'=>0,
			'offsetY'=>-14,
		),
	);
	private $scoreCardInfo = array();

	public function actionIndex() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Registration');
		if ($model->competition_id === null) {
			$model->competition_id = 0;
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionExport() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$model->formatEvents();
		if (isset($_POST['event'])) {
			$this->export($model, $this->aPost('event'), $this->iPost('all'), $this->iPost('xlsx'), $this->iPost('extra'), $this->sPost('order'));
		}
		$exportFormsts = Events::getAllExportFormats();
		$this->render('export', array(
			'model'=>$model,
			'competition'=>$model,
			'exportFormsts'=>$exportFormsts,
		));
	}

	public function actionExportLiveData() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$model->formatEvents();
		if (Yii::app()->request->getRequestType() === 'POST') {
			$this->exportLiveData($model, $this->iPost('xlsx'));
		}
		$this->render('exportLiveData', array(
			'model'=>$model,
			'competition'=>$model,
		));
	}

	public function actionLiveScoreCard() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (isset($_POST['event'])) {
			$this->pagePerStack = $this->iPost('stack', 10);
			$this->exportLiveScoreCard($model, $this->sPost('event'), $this->sPost('round'));
		}
		$this->render('scoreCard', array(
			'model'=>$model,
			'competition'=>$model,
		));
	}

	public function actionScoreCard() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (isset($_POST['order'])) {
			$this->pagePerStack = $this->iPost('stack', 50);
			$this->exportAllScoreCard($model, $this->iPost('all'), $this->sPost('order'), $this->sPost('split'), $this->sPost('direction'));
		}
		$this->render('scoreCard', array(
			'model'=>$model,
			'competition'=>$model,
		));
	}

	public function export($competition, $exportFormsts, $all = false, $xlsx = false, $extra = false, $order = 'date') {
		$registrations = Registration::getRegistrations($competition, $all, $order);
		$template = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data.results') . '.xls');
		$export = new PHPExcel();
		$export->getProperties()
			->setCreator(Yii::app()->params->author)
			->setLastModifiedBy(Yii::app()->params->author)
			->setTitle($competition->wca_competition_id ?: $competition->name)
			->setSubject($competition->name);
		$export->removeSheetByIndex(0);
		//注册页
		$sheet = $template->getSheet(0);
		$sheet->setCellValue('A1', $competition->wca_competition_id ?: $competition->name);
		$events = $competition->getRegistrationEvents();
		$col = 'J';
		$cubecompsEvents = array(
			'333'=>'3x3',
			'444'=>'4x4',
			'555'=>'5x5',
			'666'=>'6x6',
			'777'=>'7x7',
			'222'=>'2x2',
			'333bf'=>'333bld',
			'333fm'=>'fmc',
			'minx'=>'mega',
			'pyram'=>'pyra',
			'444bf'=>'444bld',
			'555bf'=>'555bld',
			'333mbf'=>'333mlt',
		);
		foreach ($events as $event=>$data) {
			$sheet->setCellValue($col . 2, "=SUM({$col}4:{$col}" . (count($registrations) + 4) . ')');
			$sheet->setCellValue($col . 3, isset($cubecompsEvents[$event]) ? $cubecompsEvents[$event] : $event);
			$sheet->getColumnDimension($col)->setWidth(5.5);
			$col++;
		}
		foreach ($registrations as $key=>$registration) {
			$user = $registration->user;
			$row = $key + 4;
			$sheet->setCellValue('A' . $row, $registration->number)
				->setCellValue('B' . $row, $user->getCompetitionName())
				->setCellValue('C' . $row, $user->country->name)
				->setCellValue('D' . $row, $user->wcaid)
				->setCellValue('E' . $row, $user->getWcaGender())
				->setCellValue('F' . $row, PHPExcel_Shared_Date::FormattedPHPToExcel(
					date('Y', $user->birthday),
					date('m', $user->birthday),
					date('d', $user->birthday)
				));
			$col = 'J';
			foreach ($events as $event=>$data) {
				if (in_array("$event", $registration->events)) {
					$sheet->setCellValue($col . $row, 1);
				}
				$col++;
			}
			if ($extra) {
				$col++;
				$fee = $registration->getTotalFee();
				if ($registration->isPaid()) {
					$fee .= Yii::t('common', ' (paid)');
				}
				$sheet->setCellValue($col . $row, $fee);
				$col++;
				$sheet->setCellValue($col . $row, $user->mobile);
				$col++;
				$sheet->setCellValue($col . $row, $user->email);
				$col++;
				$sheet->setCellValue($col . $row, $registration->comments);
			}
			if (!$registration->isAccepted()) {
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray(array(
					'fill'=>array(
						'type'=>PHPExcel_Style_Fill::FILL_SOLID,
						'color'=>array(
							'argb'=>'FFFFFF00',
						),
					),
				));
			}
		}
		$export->addExternalSheet($sheet);
		//各个项目
		foreach ($exportFormsts as $event=>$rounds) {
			$count = count($rounds);
			foreach ($rounds as $round=>$format) {
				if ($round == $count - 1) {
					$round = 'f';
				} else {
					$round++;
				}
				$sheet = $template->getSheetByName($format);
				if ($sheet === null) {
					continue;
				}
				$sheet = clone $sheet;
				$sheet->setTitle("{$event}-{$round}");
				$template->addSheet($sheet);
				$sheet->setCellValue('A1', Events::getFullEventName($event) . ' - ' . Rounds::getFullRoundName($round));
				if ($round == 1 || $count == 1) {
					$row = 5;
					foreach ($registrations as $registration) {
						if (!in_array("$event", $registration->events)) {
							continue;
						}
						$user = $registration->user;
						$sheet->setCellValue('B' . $row, $user->getCompetitionName())
							->setCellValue('C' . $row, $user->country->name)
							->setCellValue('D' . $row, $user->wcaid);
						if ($row > 5) {
							$formula = $sheet->getCell('A' . ($row - 1))->getValue();
							$formula = strtr($formula, array(
								'-4'=>'_temp_',
								$row - 1=>$row,
								$row - 2=>$row - 1,
								$row=>$row+1,
							));
							$formula = str_replace('_temp_', '-4', $formula);
							$sheet->setCellValue('A' . $row, $formula);
						}
						if (!$registration->isAccepted()) {
							$sheet->getStyle("A{$row}:D{$row}")->applyFromArray(array(
								'fill'=>array(
									'type'=>PHPExcel_Style_Fill::FILL_SOLID,
									'color'=>array(
										'argb'=>'FFFFFF00',
									),
								),
							));
						}
						$row++;
					}
				}
				$export->addExternalSheet($sheet);
			}
		}
		$this->exportToExcel($export, 'php://output', $competition->name, $xlsx);
	}

	public function exportLiveData($competition, $xlsx = false) {
		$liveResults = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$competition->id,
		), array(
			'condition'=>'best != 0',
		));
		$registrations = array();
		$events = array();
		foreach ($liveResults as $liveResult) {
			$key = $liveResult->user_type . '_' . $liveResult->user->id;
			$round = $liveResult->eventRound;
			if (!isset($registrations[$key])) {
				$registrations[$key] = array(
					'user'=>$liveResult->user,
					'number'=>$liveResult->number,
					'events'=>array(),
				);
			}
			if (!isset($events[$liveResult->event])) {
				$events[$liveResult->event] = array(
					'event'=>$liveResult->wcaEvent,
					'rounds'=>array(),
				);
			}
			if (!isset($events[$liveResult->event]['rounds'][$liveResult->round])) {
				$events[$liveResult->event]['rounds'][$liveResult->round] = array(
					'round'=>$liveResult->wcaRound,
					'format'=>$round->format,
					'results'=>array(),
				);
			}
			$events[$liveResult->event]['rounds'][$liveResult->round]['results'][] = $liveResult;
			$registrations[$key]['events'][$liveResult->event] = $liveResult->event;
		}
		usort($registrations, function($regA, $regB) {
			return $regA['number'] - $regB['number'];
		});
		//sort event
		uasort($events, function($eventA, $eventB) {
			if ($eventA['event'] && $eventB['event']) {
				$temp = $eventA['event']->rank - $eventB['event']->rank;
			} elseif ($eventA && !$eventB) {
				$temp = -1;
			} elseif (!$eventA && $eventB) {
				$temp = 1;
			} else {
				$temp = 0;
			}
			return $temp;
		});
		$template = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data.results') . '.xls');
		$export = new PHPExcel();
		$export->getProperties()
			->setCreator(Yii::app()->params->author)
			->setLastModifiedBy(Yii::app()->params->author)
			->setTitle($competition->wca_competition_id ?: $competition->name)
			->setSubject($competition->name);
		$export->removeSheetByIndex(0);
		//注册页
		$sheet = $template->getSheet(0);
		$sheet->setCellValue('A1', $competition->wca_competition_id ?: $competition->name);
		$col = 'J';
		$cubecompsEvents = array(
			'333'=>'3x3',
			'444'=>'4x4',
			'555'=>'5x5',
			'666'=>'6x6',
			'777'=>'7x7',
			'222'=>'2x2',
			'333bf'=>'333bld',
			'333fm'=>'fmc',
			'minx'=>'mega',
			'pyram'=>'pyra',
			'444bf'=>'444bld',
			'555bf'=>'555bld',
			'333mbf'=>'333mlt',
		);
		foreach ($events as $event=>$value) {
			$sheet->setCellValue($col . 2, "=SUM({$col}4:{$col}" . (count($registrations) + 4) . ')');
			$sheet->setCellValue($col . 3, $value['event'] ? (isset($cubecompsEvents[$value['event']->id]) ? $cubecompsEvents[$value['event']->id] : $value['event']->id) : $event);
			$sheet->getColumnDimension($col)->setWidth(5.5);
			$col++;
		}
		foreach ($registrations as $key=>$registration) {
			$user = $registration['user'];
			$row = $key + 4;
			$sheet->setCellValue('A' . $row, $registration['number'])
				->setCellValue('B' . $row, $user->getCompetitionName())
				->setCellValue('C' . $row, $user->country->name)
				->setCellValue('D' . $row, $user->wcaid)
				->setCellValue('E' . $row, $user->getWcaGender())
				->setCellValue('F' . $row, PHPExcel_Shared_Date::FormattedPHPToExcel(
					date('Y', $user->birthday),
					date('m', $user->birthday),
					date('d', $user->birthday)
				));
			$col = 'J';
			foreach ($events as $event=>$value) {
				if (in_array($event, $registration['events'])) {
					$sheet->setCellValue($col . $row, 1);
				}
				$col++;
			}
		}
		$export->addExternalSheet($sheet);
		//各个项目
		$compare = function($resA, $resB) {
			$temp = 0;
			$format = $resA->eventRound->format;
			if ($format == 'm' || $format == 'a') {
				if ($resA->average > 0 && $resB->average <= 0) {
					return -1;
				}
				if ($resB->average > 0 && $resA->average <= 0) {
					return 1;
				}
				$temp = $resA->average - $resB->average;
			}
			if ($temp == 0) {
				if ($resA->best > 0 && $resB->best <= 0) {
					return -1;
				}
				if ($resB->best > 0 && $resA->best <= 0) {
					return 1;
				}
				$temp = $resA->best - $resB->best;
			}
			if ($temp == 0) {
				$temp = $resA->user->name < $resB->user->name ? -1 : 1;
			}
			return $temp;
		};
		foreach ($events as $event=>$value) {
			usort($value['rounds'], function($roundA, $roundB) {
				return $roundA['round']->rank - $roundB['round']->rank;
			});
			foreach ($value['rounds'] as $round) {
				$formatName = Events::getExportFormat($event, $round['format']);
				$sheet = $template->getSheetByName($formatName);
				if ($sheet === null) {
					continue;
				}
				$sheet = clone $sheet;
				$sheet->setTitle("{$event}-{$round['round']->id}");
				$template->addSheet($sheet);
				$sheet->setCellValue('A1', Events::getFullEventName($event) . ' - ' . Rounds::getFullRoundName($round['round']->id));
				usort($round['results'], $compare);
				$row = 5;
				$num = Formats::getFormatNum($round['format']);
				foreach ($round['results'] as $result) {
					//user info
					$user = $result->user;
					$sheet->setCellValue('B' . $row, $user->getCompetitionName())
						->setCellValue('C' . $row, $user->country->name)
						->setCellValue('D' . $row, $user->wcaid)
						->setCellValue('Z' . $row, $result->number);
					//result
					$col = 'E';
					if ($result->event === '333mbf') {
						for ($i = 1; $i <= $result->eventRound->format; $i++) {
							$value = $result->{'value' . $i};
							if ($value == -1 || $value == -2) {
								//tried
								$sheet->setCellValue($col . $row, LiveResult::formatTime($value, $result->event));
								$col++;
								//solved
								$sheet->setCellValue($col . $row, 0);
								$col++;
								//seconds
								$sheet->setCellValue($col . $row, 0);
								$col++;
								$col++;

							} else {
								$difference = 99 - substr($value, 0, 2);
								$missed = intval(substr($value, -2));
								$seconds = intval(substr($value, 3, -2));
								$solved = $difference + $missed;
								$tried = $solved + $missed;
								//tried
								$sheet->setCellValue($col . $row, $tried);
								$col++;
								//solved
								$sheet->setCellValue($col . $row, $solved);
								$col++;
								//seconds
								$sheet->setCellValue($col . $row, $seconds);
								$col++;
								$col++;
							}
						}
					} else {
						for ($i = 1; $i <= $num; $i++) {
							$sheet->setCellValue($col . $row, LiveResult::formatTime($result->{'value' . $i}, $result->event));
							$col++;
						}
					}
					if ($row > 5) {
						$formula = $sheet->getCell('A' . ($row - 1))->getValue();
						$formula = strtr($formula, array(
							'-4'=>'_temp_',
							$row - 1=>$row,
							$row - 2=>$row - 1,
							$row=>$row+1,
						));
						$formula = str_replace('_temp_', '-4', $formula);
						$sheet->setCellValue('A' . $row, $formula);
						//formula for best and average
						while ($col != 'R') {
							$formula = $sheet->getCell($col . ($row - 1))->getValue();
							if (strpos($formula, '=') === 0) {
								$formula = strtr($formula, array(
									$row - 1=>$row,
								));
								$sheet->setCellValue($col . $row, $formula);
							}
							$col++;
						}
					}
					$row++;
				}
				$export->addExternalSheet($sheet);
			}
		}
		$this->exportToExcel($export, 'php://output', $competition->name, $xlsx, true);
	}

	public function exportLiveScoreCard($competition, $event, $round) {
		$liveResults = LiveResult::model()->with('user')->findAllByAttributes([
			'competition_id'=>$competition->id,
			'event'=>$event,
			'round'=>$round,
		], [
			'order'=>'number'
		]);
		$this->exportScoreCard($competition, $liveResults);
	}

	public function exportAllScoreCard($competition, $all = false, $order = 'date', $split = 'user', $direction = 'vertical') {
		$registrations = Registration::getRegistrations($competition, $all, $order);
		$this->exportScoreCard($competition, $registrations, $split, $direction);
	}

	public function exportScoreCard($competition, $registrations, $split = 'user', $direction = 'vertical') {
		$tempPath = Yii::app()->runtimePath;
		$templatePath = APP_PATH . '/public/static/score-card.xlsx';
		$scoreCard = PHPExcel_IOFactory::load($templatePath);
		$scoreCard->getProperties()
			->setCreator(Yii::app()->params->author)
			->setLastModifiedBy(Yii::app()->params->author)
			->setTitle($competition->wca_competition_id ?: $competition->name)
			->setSubject($competition->name);
		$sheet = $scoreCard->getSheet(0);
		$drawingCollection = $sheet->getDrawingCollection();
		foreach ($drawingCollection as $i=>$drawing) {
			$drawing->setWidthAndHeight(0, 0);
		}
		$rowHeights = array();
		$xfIndexes = array();
		$oneCardRow = 11;
		for ($row = 1; $row <= $oneCardRow; $row++) {
			$height = $sheet->getRowDimension($row)->getRowHeight();
			if ($height === -1) {
				$height = isset($rowHeights[$row - 1]) ? $rowHeights[$row - 1] - 1 : 10;
			}
			$rowHeights[$row] = $height;
			$xfIndexes[$row] = array();
			for ($col = 'A'; strcmp($col, 'K') != 0; $col++) {
				$xfIndexes[$row][$col] = $sheet->getCell($col . $row)->getXfIndex();
			}
		}
		//fix the height of last row
		$rowHeights[$oneCardRow] = 10.75;
		$staticCells = array(
			'A2', 'D2', 'F2',
			'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3', 'H3', 'I3', 'J3',
			'A4', 'B4', 'C4', 'D4', 'E4', 'F4', 'G4', 'H4', 'I4', 'J4',
			'A5', 'A6', 'A7', 'A8', 'A9',
			'A10',
		);
		$values = array();
		foreach ($staticCells as $cell) {
			$value = $sheet->getCell($cell)->getValue();
			$template = preg_replace('{\d+}', '{row}', $cell);
			$row = preg_replace('{[A-Z]}', '', $cell);
			$values[] = array(
				'value'=>$value,
				'template'=>$template,
				'row'=>$row,
			);
		}
		$this->scoreCardInfo = compact('rowHeights', 'xfIndexes', 'values', 'drawingCollection');
		$i = 0;
		$count = 0;
		if ($split === 'event') {
			$competition->formatEvents();
			foreach ($competition->events as $event=>$value) {
				if ($value['round'] <= 0) {
					continue;
				}
				if ($event === '333fm') {
					continue;
				}
				foreach ($registrations as $registration) {
					if (!in_array("$event", $registration->events)) {
						continue;
					}
					$this->fillScoreCard($competition, $sheet, $direction, $i, $registration, $event);
					$this->splitScoreCard($scoreCard, $sheet, $count, $i, $competition);
				}
			}
		} else {
			foreach ($registrations as $registration) {
				foreach ($registration->events as $event) {
					if ($event === '333fm') {
						continue;
					}
					$this->fillScoreCard($competition, $sheet, $direction, $i, $registration, $event);
					$this->splitScoreCard($scoreCard, $sheet, $count, $i, $competition);
				}
			}
		}
		if ($direction !== 'horizontal') {
			$temp = self::CARD_PER_PAGE * $this->pagePerStack;
			$total = ceil($i / $temp) * $temp;
		} else {
			$total = ceil($i / self::CARD_PER_PAGE) * self::CARD_PER_PAGE;
		}
		while ($i < $total) {
			$this->fillScoreCard($competition, $sheet, $direction, $i);
			$i++;
		}
		if ($count == 0) {
			$sheet->getPageSetup()->setPrintArea('A1:J' . (ceil($i / self::CARD_PER_PAGE) * self::CARD_PER_PAGE * self::ROW_PER_CARD));
			$this->exportToExcel($scoreCard, 'php://output', $competition->name);
		} else {
			//压缩成zip
			$path = $tempPath . '/' . $competition->name . ".$count.xlsx";
			$this->exportToExcel($scoreCard, $path);
			//释放内存
			$scoreCard->disconnectWorksheets();
			unset($scoreCard, $sheet);
			$count++;
			$zip = new ZipArchive();
			$tempName = tempnam($tempPath, 'scoreCardTmp');
			if ($zip->open($tempName, ZipArchive::CREATE) !== true) {
				throw new CHttpException(500, '创建压缩文件失败');
			}
			$dir = 'score-card';
			$zip->addEmptyDir($dir);
			for ($i = 0; $i < $count; $i++) {
				$path = $tempPath . '/' . $competition->name . ".$i.xlsx";
				$zip->addFile($path, $dir. '/' . basename($path));
			}
			$zip->close();
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment;filename="' . $competition->name . '.zip"');
			readfile($tempName);
			//删除临时文件
			for ($i = 0; $i < $count; $i++) {
				$path = $tempPath . '/' . $competition->name . ".$i.xlsx";
				unlink($path);
			}
			unlink($tempName);
			exit;
		}
	}

	private function splitScoreCard(&$scoreCard, &$sheet, &$count, &$i, $competition) {
		$i++;
		//200页做个分割
		if ($i == 600) {
			$path = Yii::app()->runtimePath . '/' . $competition->name . ".$count.xlsx";
			$sheet->getPageSetup()->setPrintArea('A1:J' . (ceil($i / self::CARD_PER_PAGE) * self::CARD_PER_PAGE * self::ROW_PER_CARD));
			$this->exportToExcel($scoreCard, $path);
			//释放内存
			$scoreCard->disconnectWorksheets();
			// unset($scoreCard, $sheet);
			$count++;
			$i = 0;
			//新开excel
			$scoreCard = PHPExcel_IOFactory::load(APP_PATH . '/public/static/score-card.xlsx');
			$scoreCard->getProperties()
				->setCreator(Yii::app()->params->author)
				->setLastModifiedBy(Yii::app()->params->author)
				->setTitle($competition->wca_competition_id ?: $competition->name)
				->setSubject($competition->name);
			$sheet = $scoreCard->getSheet(0);
			//修复图片宽高及偏移
			$drawingCollection = $sheet->getDrawingCollection();
			foreach ($drawingCollection as $drawing) {
				$drawing->setWidthAndHeight(0, 0);
			}
			$this->scoreCardInfo['drawingCollection'] = $drawingCollection;
		}
	}

	private function fillScoreCard($competition, $sheet, $direction, $i, $registration = null, $event= '') {
		$oneCardRow = self::ROW_PER_CARD;
		if ($direction === 'horizontal') {
			$baseRow = $i * $oneCardRow;
		} else {
			//n张一摞
			$temp = self::CARD_PER_PAGE * $this->pagePerStack;
			$group = floor($i / $temp);
			$subGroup = $i % $temp;
			$x = floor($subGroup / $this->pagePerStack);
			$y = $subGroup % $this->pagePerStack;
			$baseRow = $oneCardRow * ($group * $temp + $y * self::CARD_PER_PAGE + $x);
		}
		//merge cells
		//wcaid
		$sheet->mergeCells(sprintf('I%d:J%d', $baseRow + 1, $baseRow + 1));
		//event
		$sheet->mergeCells(sprintf('B%d:C%d', $baseRow + 2, $baseRow + 2));
		//name
		$sheet->mergeCells(sprintf('G%d:I%d', $baseRow + 2, $baseRow + 2));

		//调整各行高度及样式
		for ($j = 1; $j <= $oneCardRow; $j++) {
			$row = $baseRow + $j;
			$sheet->getRowDimension($row)->setRowHeight($this->scoreCardInfo['rowHeights'][$j]);
			foreach ($this->scoreCardInfo['xfIndexes'][$j] as $col=>$xfIndex) {
				$sheet->getCell($col . $row)->setXfIndex($xfIndex);
			}
		}
		//填写固定内容
		foreach ($this->scoreCardInfo['values'] as $value) {
			$row = $baseRow + $value['row'];
			$cell = str_replace('{row}', $row, $value['template']);
			$sheet->setCellValue($cell, $value['value']);
		}
		//比赛名字
		$row = $baseRow + 1;
		$sheet->setCellValue("A{$row}", sprintf('%s成绩条 - %s Score Card', $competition->name_zh, $competition->name));
		if ($registration !== null) {
			$user = $registration->user;
			$sheet->setCellValue("I{$row}", $user->wcaid);
			//项目、轮次、编号等
			$row = $baseRow + 2;
			$eventName = Events::getFullEventName($event);
			$eventName = sprintf('%s %s', Yii::t('event', $eventName), $event);
			$sheet->setCellValue("B{$row}", $eventName);
			$sheet->setCellValue("E{$row}", '1st');
			$sheet->setCellValue("J{$row}", 'No.' . $registration->number);
			$sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->setCellValue("G{$row}", $user->country_id <= 4 && $user->name_zh ? $user->name_zh : $user->name);
		}
		//8个图片
		$row = $baseRow + 3;
		$col = 'D';
		for ($j = 0; $j < 4; $j++) {
			$drawing = new PHPExcelDrawing();
			$drawing->setImageIndex($this->scoreCardInfo['drawingCollection'][$j]->getImageIndex());
			$drawing->setWorksheet($sheet);
			$drawing->setPath($this->scoreCardInfo['drawingCollection'][$j]->getPath(), false);
			$drawing->setResizeProportional(false);
			$drawing->setCoordinates("{$col}{$row}");
			$drawing->setWidth($this->imageStyle[0]['width'])->setHeight($this->imageStyle[0]['height']);
			$drawing->setOffsetX($this->imageStyle[0]['offsetX'])->setOffsetY($this->imageStyle[0]['offsetY']);
			$col++;
		}
		$row = $baseRow + 4;
		$col = 'D';
		for ($j = 4; $j < 8; $j++) {
			$drawing = new PHPExcelDrawing();
			$drawing->setImageIndex($this->scoreCardInfo['drawingCollection'][$j]->getImageIndex());
			$drawing->setWorksheet($sheet);
			$drawing->setPath($this->scoreCardInfo['drawingCollection'][$j]->getPath(), false);
			$drawing->setResizeProportional(false);
			$drawing->setCoordinates("{$col}{$row}");
			$drawing->setWidth($this->imageStyle[1]['width'])->setHeight($this->imageStyle[1]['height']);
			$drawing->setOffsetX($this->imageStyle[1]['offsetX'])->setOffsetY($this->imageStyle[1]['offsetY']);
			$col++;
		}
	}

	public function actionSendNotice() {
		$id = $this->iGet('id');
		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$competition->formatEvents();
		$registration = new Registration();
		$registration->unsetAttributes();
		$registration->competition_id = $id;
		$model = new SendNoticeForm();
		if (isset($_POST['SendNoticeForm'])) {
			$model->attributes = $_POST['SendNoticeForm'];
			if ($model->validate() && $model->send($competition)) {
				Yii::app()->user->setFlash('success', '发送成功！');
				$this->redirect(array('/board/registration/index', 'Registration'=>array('competition_id'=>$id)));
			}
		}
		$this->render('sendNotice', array(
			'model'=>$model,
			'competition'=>$competition,
			'registration'=>$registration,
		));
	}

	public function actionPreviewNotice() {
		$id = $this->iGet('id');
		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			throw new CHttpException(404, '未知比赛ID');
		}
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id])) {
			throw new CHttpException(403, '权限不足');
		}
		$competition->formatEvents();
		$registration = new Registration();
		$registration->unsetAttributes();
		$registration->competition_id = $id;
		$model = new SendNoticeForm();
		if (isset($_POST['SendNoticeForm'])) {
			$model->attributes = $_POST['SendNoticeForm'];
		}
		echo json_encode($model->getPreview($competition));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (isset($_POST['Registration'])) {
			$model->attributes = $_POST['Registration'];
			$model->avatar_type = isset($_POST['Registration']['avatar_type']) ? $_POST['Registration']['avatar_type'] : 0;
			if ($model->competition->require_avatar && $model->avatar_type == Registration::AVATAR_TYPE_NOW) {
				$model->avatar_id = $model->user->avatar_id;
			}
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新报名信息成功');
				$this->redirect(array('/board/registration/index', 'Registration'=>array(
					'competition_id'=>$model->competition_id,
				)));
			}
		}
		$model->formatEvents();
		$model->competition->formatEvents();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionShow() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->status = Registration::STATUS_ACCEPTED;
		$model->save();
		Yii::app()->user->setFlash('success', '通过报名成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Registration::STATUS_WAITING;
		$model->save();
		Yii::app()->user->setFlash('success', '取消报名成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionPaid() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->paid = Registration::PAID;
		$model->save();
		Yii::app()->user->setFlash('success', $model->user->name . '已付！');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionUnpaid() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->paid = Registration::UNPAID;
		$model->save();
		Yii::app()->user->setFlash('success', $model->user->name . '未付！');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionToggle() {
		$id = $this->iRequest('id');
		$attribute = $this->sRequest('attribute');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'Not found');
		}
		$competition = $model->competition;
		if ($competition === null) {
			throw new CHttpException(404, 'Not found');
		}
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id])) {
			throw new CHttpException(401, 'Unauthorized');
		}
		if ($this->user->role != User::ROLE_ADMINISTRATOR && $attribute == 'status' && !$competition->canRegister()) {
			throw new CHttpException(401, '报名已截止，如需变更请联系代表或管理员');
		}
		$model->$attribute = 1 - $model->$attribute;
		if ($attribute == 'status' && $model->isAccepted()) {
			$model->total_fee = $model->getTotalFee(true);
			$model->accept();
		} else {
			$model->save();
		}
		$this->ajaxOk(array(
			'value'=>$model->$attribute,
		));
	}
}
