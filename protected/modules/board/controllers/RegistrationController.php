<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class RegistrationController extends AdminController {

	const ROW_PER_CARD = 11;
	const CARD_PER_PAGE = 3;
	const TIME_SLOT = '<b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>';

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

	public function accessRules() {
		return [
			[
				'allow',
				'roles'=>[
					'role'=>User::ROLE_ORGANIZER,
				],
			],
			[
				'deny',
				'users'=>['*'],
			],
		];
	}

	public function actionIndex() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Registration');
		if ($model->competition_id === null) {
			$model->competition_id = 0;
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
		}
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionSignin() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Registration');
		if ($model->competition === null || !$model->competition->show_qrcode) {
			$this->redirect(array('/board/registration/index'));
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$scanAuth = ScanAuth::getCompetitionAuth($model->competition) ?: ScanAuth::generateCompetitionAuth($model->competition);
		$this->render('signin', array(
			'model'=>$model,
			'scanAuth'=>$scanAuth,
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
			$this->exportAllScoreCard($model, $this->iPost('all'), $this->sPost('order'), $this->sPost('split'), $this->sPost('direction'), $this->sPost('group'));
		}
		$this->render('scoreCard', array(
			'model'=>$model,
			'competition'=>$model,
		));
	}

	public function export($competition, $exportFormsts, $all = false, $xlsx = false, $extra = false, $order = 'date') {
		$registrations = Registration::getRegistrations($competition, $all, $order);
		$template = IOFactory::load(Yii::getPathOfAlias('application.data.results') . '.xlsx');
		$export = new Spreadsheet();
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
		$col = 'H';
		foreach ($events as $event=>$data) {
			$sheet->setCellValue($col . 2, "=SUM({$col}4:{$col}" . (count($registrations) + 4) . ')');
			$sheet->setCellValue($col . 3, $event);
			$sheet->getColumnDimension($col)->setWidth(5.5);
			$col++;
		}
		foreach ($registrations as $key=>$registration) {
			$user = $registration->user;
			$row = $key + 4;
			$sheet->setCellValue('A' . $row, $registration->number)
				->setCellValue('B' . $row, $user->getCompetitionName())
				->setCellValue('C' . $row, $user->country->wcaCountry->id)
				->setCellValue('D' . $row, $user->wcaid)
				->setCellValue('E' . $row, $user->getWcaGender())
				->setCellValue('F' . $row, SharedDate::formattedPHPToExcel(
					date('Y', $user->birthday),
					date('m', $user->birthday),
					date('d', $user->birthday)
				));
			$col = 'H';
			foreach ($events as $event=>$data) {
				if ($registration->hasRegistered($event)) {
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
				$sheet->setCellValueExplicit($col . $row, $user->mobile, DataType::TYPE_STRING);
				$col++;
				$sheet->setCellValue($col . $row, $user->email);
				$col++;
				$sheet->setCellValue($col . $row, $registration->comments);
				if ($competition->t_shirt) {
					$col++;
					$col++;
					$sheet->setCellValue($col . $row, $registration->getTShirtSizeText());
				}
				if ($competition->staff) {
					$col++;
					$col++;
					$sheet->setCellValue($col . $row, $registration->getStaffTypeText());
					$col++;
					$sheet->setCellValue($col . $row, $registration->staff_statement);
				}
				if ($competition->fill_passport) {
					$col++;
					$col++;
					$sheet->setCellValue($col . $row, $user->getPassportTypeText());
					$col++;
					$sheet->setCellValueExplicit($col . $row, $user->passport_number, DataType::TYPE_STRING);
				}
				if ($competition->entourage_limit && $registration->has_entourage) {
					$col++;
					$col++;
					$sheet->setCellValue($col . $row, $registration->entourage_name);
					$col++;
					$sheet->setCellValue($col . $row, $registration->getPassportTypeText());
					$col++;
					$sheet->setCellValueExplicit($col . $row, $registration->entourage_passport_number, DataType::TYPE_STRING);
					$col++;
					$sheet->setCellValueExplicit($col . $row, $registration->guest_paid == Registration::YES ? '已支付' : '未支付');
				}
			}
			if (!$registration->isAccepted()) {
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray(array(
					'fill'=>array(
						'type'=>Fill::FILL_SOLID,
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
				$sheet->setCellValue('A1', Events::getFullEventName($event) . ' - ' . RoundTypes::getFullRoundName($round));
				if ($round == 1 || $count == 1) {
					$row = 5;
					foreach ($registrations as $registration) {
						if (!$registration->hasRegistered($event)) {
							continue;
						}
						$user = $registration->user;
						$sheet->setCellValue('B' . $row, $user->getCompetitionName())
							->setCellValue('C' . $row, $user->country->wcaCountry->id)
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
									'type'=>Fill::FILL_SOLID,
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
		$template = IOFactory::load(Yii::getPathOfAlias('application.data.results') . '.xlsx');
		$export = new Spreadsheet();
		$export->getProperties()
			->setCreator(Yii::app()->params->author)
			->setLastModifiedBy(Yii::app()->params->author)
			->setTitle($competition->wca_competition_id ?: $competition->name)
			->setSubject($competition->name);
		$export->removeSheetByIndex(0);
		//注册页
		$sheet = $template->getSheet(0);
		$sheet->setCellValue('A1', $competition->wca_competition_id ?: $competition->name);
		$col = 'H';
		foreach ($events as $event=>$value) {
			$sheet->setCellValue($col . 2, "=SUM({$col}4:{$col}" . (count($registrations) + 4) . ')');
			$sheet->setCellValue($col . 3, $value['event'] ? $value['event']->id : $event);
			$sheet->getColumnDimension($col)->setWidth(5.5);
			$col++;
		}
		foreach ($registrations as $key=>$registration) {
			$user = $registration['user'];
			$row = $key + 4;
			$sheet->setCellValue('A' . $row, $registration['number'])
				->setCellValue('B' . $row, $user->getCompetitionName())
				->setCellValue('C' . $row, $user->country->wcaCountry->id)
				->setCellValue('D' . $row, $user->wcaid)
				->setCellValue('E' . $row, $user->getWcaGender())
				->setCellValue('F' . $row, SharedDate::formattedPHPToExcel(
					date('Y', $user->birthday),
					date('m', $user->birthday),
					date('d', $user->birthday)
				));
			$col = 'H';
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
				$roundName = RoundTypes::getFullRoundName($round['round']->id);
				if (in_array($round['round']->id, ['d', 'c', 'e', 'g'])) {
					$roundName = 'Combined ' . $roundName;
				}
				$sheet->setCellValue('A1', Events::getEventName($event) . ' - ' . $roundName);
				usort($round['results'], $compare);
				$row = 5;
				$num = Formats::getFormatNum($round['format']);
				foreach ($round['results'] as $result) {
					//user info
					$user = $result->user;
					$sheet->setCellValue('B' . $row, $user->getCompetitionName())
						->setCellValue('C' . $row, $user->country->wcaCountry->id)
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
		$byGroup = GroupSchedule::model()->countByAttributes([
			'competition_id'=>$competition->id,
			'event'=>$event,
			'round'=>$round,
		]) > 0;
		$this->exportScoreCard($competition, $liveResults, 'user', 'vertical', $competition->getScheduledRound($event, $round), $byGroup);
	}

	public function exportAllScoreCard($competition, $all = false, $order = 'date', $split = 'user', $direction = 'vertical', $byGroup = false) {
		$registrations = Registration::getRegistrations($competition, $all, $order);
		$this->exportScoreCard($competition, $registrations, $split, $direction, null, $byGroup);
	}

	public function exportScoreCard($competition, $registrations, $split = 'user', $direction = 'vertical', $round = null, $byGroup = false) {
		$tempPath = Yii::app()->runtimePath;
		$scoreCards = [];
		if (isset($competition->associatedEvents['333mbf']) && ($round === null || $round->event != '333mbf')) {
			$mbfRound = $competition->getFirstRound('333mbf');
			$format = $mbfRound->format ?? '3';
			// deal with 1/3, 2/a
			switch ($format[strlen($format) - 1]) {
				case '2/a':
				case 'a':
					$attempt = 5;
					break;
				case '1/m':
				case 'm':
					$attempt = 3;
					break;
				default:
					$attempt = intval($format);
					break;
			}
		}
		$groupSchedules = GroupSchedule::model()->findAllByAttributes([
			'competition_id'=>$competition->id,
		], [
			'order'=>'event, `group`',
		]);
		$groups = [];
		foreach ($groupSchedules as $groupSchedule) {
			$groups[$groupSchedule->event][$groupSchedule->round][$groupSchedule->group] = array_map(function($userSchedule) {
				return $userSchedule->user_id;
			}, $groupSchedule->users);
		}
		if ($split === 'event') {
			foreach ($competition->associatedEvents as $event=>$value) {
				if ($event === '333fm') {
					continue;
				}
				if ($event === '333mbf') {
					for ($i = 0; $i < $attempt; $i++) {
						foreach ($registrations as $registration) {
							if (!$registration->hasRegistered($event)) {
								continue;
							}
							$scoreCards[] = [
								'registration'=>$registration,
								'event'=>$event,
								'start'=>$i,
								'attempt'=>$i + 1,
							];
						}
					}
				} elseif ($byGroup) {
					foreach ($groups[$event] ?? [] as $roundId=>$groupUserIds) {
						$firstRound = $competition->getFirstRound($event);
						if ($round == null && $firstRound->round != $roundId || $round != null && $round->round != $roundId) {
							continue;
						}
						foreach ($groupUserIds as $group=>$userIds) {
							foreach ($registrations as $registration) {
								if (!$registration->hasRegistered($event)) {
									continue;
								}
								if (!in_array($registration->user_id, $userIds)) {
									continue;
								}
								$scoreCards[] = [
									'registration'=>$registration,
									'event'=>$event,
									'group'=>$group,
								];
							}
						}
					}
				} else {
					foreach ($registrations as $registration) {
						if (!$registration->hasRegistered($event)) {
							continue;
						}
						$scoreCards[] = [
							'registration'=>$registration,
							'event'=>$event,
						];
					}
				}
			}
		} else {
			if ($byGroup) {
				foreach ($groups as $event=>$eventGroups) {
					foreach ($eventGroups as $roundId=>$groupUserIds) {
						$firstRound = $competition->getFirstRound($event);
						if ($round == null && $firstRound->round != $roundId || $round != null && $round->round != $roundId) {
							continue;
						}
						foreach ($groupUserIds as $group=>$userIds) {
							foreach ($registrations as $registration) {
								if (!in_array("$event", $registration->events)) {
									continue;
								}
								if (!in_array($registration->user_id, $userIds)) {
									continue;
								}
								$scoreCards[] = [
									'registration'=>$registration,
									'event'=>$event,
									'group'=>$group,
								];
							}
						}
					}
				}
			} else {
				foreach ($registrations as $registration) {
					foreach ($registration->events as $event) {
						if ($event === '333fm') {
							continue;
						}
						if ($event === '333mbf') {
							for ($i = 0; $i < $attempt; $i++) {
								$scoreCards[] = [
									'registration'=>$registration,
									'event'=>$event,
									'start'=>$i,
									'attempt'=>$i + 1,
								];
							}
						} else {
							$scoreCards[] = [
								'registration'=>$registration,
								'event'=>$event,
							];
						}
					}
				}
			}
		}
		$pdf = new \Mpdf\Mpdf();
		$pdf->useAdobeCJK = true;
		$pdf->autoScriptToLang = true;
		$pdf->autoLangToFont = true;
		// $pdf->simpleTables = true;
		$stylesheet = file_get_contents(Yii::getPathOfAlias('application.data') . '/scord-card.css');
		$pdf->WriteHTML($stylesheet, 1);
		foreach (array_chunk($scoreCards, $this->pagePerStack * self::CARD_PER_PAGE) as $scoreCards) {
			$count = count($scoreCards);
			$i = 0;
			$pagePerStack = min($this->pagePerStack, ceil($count / self::CARD_PER_PAGE));
			while ($i < $count) {
				if ($direction == 'horizontal') {
					$n = $i;
				} else {
					$j = floor($i / self::CARD_PER_PAGE);
					$k = $i % self::CARD_PER_PAGE;
					$n = intval($k * $pagePerStack + $j);
					if ($count % 3 == 1 && $k == 2) {
						$n--;
					}
				}
				$scoreCard = $scoreCards[$n];
				$this->fillScoreCard($pdf, $competition, $scoreCard, $round);
				$i++;
				if ($i % self::CARD_PER_PAGE == 0 && $i < $count) {
					$pdf->AddPage();
				}
			}
		}
		$name = $competition->name_zh;
		if ($round !== null) {
			$name .= Events::getFullEventName($round->event) . Yii::t('RoundTypes', RoundTypes::getFullRoundName($round->round));
		}
		$pdf->Output($name . '成绩条.pdf', 'D');
	}

	private function fillScoreCard($pdf, $competition, $scoreCard, $round) {
		$registration = $scoreCard['registration'];
		$user = $registration->user;
		$event = $scoreCard['event'];
		$group = $scoreCard['group'] ?? '';
		if ($round === null) {
			$round = $competition->getFirstRound($scoreCard['event']);
		}
		$format = $round->format ?? 'a';
		// deal with 1/3, 2/a
		switch ($format[strlen($format) - 1]) {
			case '2/a':
			case 'a':
				$attempt = 5;
				break;
			case '1/m':
			case 'm':
				$attempt = 3;
				break;
			default:
				$attempt = intval($format);
				break;
		}
		if ($event === '333mbf') {
			$attempt = 1;
		}
		$imageDir = Yii::getPathOfAlias('application.data.penalty-images');
		ob_start();
		ob_implicit_flush(false);
		$class = 'attempt-' . $attempt;
		if (strpos($format, '/') !== false) {
			$class .= '-has-cutoff';
		}
		echo CHtml::openTag('table', [
			'class'=>$class,
		]);
		echo CHtml::openTag('tbody');

		//competition name and wcaid
		echo CHtml::openTag('tr');
		echo CHtml::openTag('td', [
			'colspan'=>8,
			'class'=>'tal no-bd'
		]);
		echo sprintf('%s - %s', $competition->name_zh, $competition->name);
		echo CHtml::closeTag('td');
		echo CHtml::openTag('td', [
			'colspan'=>2,
			'class'=>'no-bd'
		]);
		echo $user->wcaid;
		echo CHtml::closeTag('td');
		echo CHtml::closeTag('tr');

		//event, round, name and number
		echo CHtml::openTag('tr');
		echo CHtml::tag('td', [
		], '项目<br>Event');
		echo CHtml::openTag('td', [
			'colspan'=>2,
			'class'=>'bdld',
		]);
		echo Events::getFullEventName($event) . ' ' . $event;
		echo CHtml::closeTag('td');
		$roundId = $round->round ?? '';
		switch ($roundId) {
			case 'd':
				$roundId = 1;
				break;
			case 'c':
				$roundId = 'f';
				break;
			case 'e':
				$roundId = 2;
				break;
			case 'g':
				$roundId = 3;
				break;
		}
		echo CHtml::tag('td', [
		], '轮次<br>Round');
		echo CHtml::openTag('td', [
			'class'=>'bdld',
		]);
		echo $roundId;
		if ($group) {
			echo '-' . $group;
		}
		echo CHtml::closeTag('td');
		echo CHtml::tag('td', [
		], '姓名<br>Name');
		echo CHtml::openTag('td', [
			'colspan'=>3,
			'class'=>'bdld',
		]);
		echo $user->country_id <= 4 && $user->name_zh ? $user->name_zh : $user->name;
		echo CHtml::closeTag('td');
		echo CHtml::tag('td', [
			'class'=>'bdr'
		], 'No.' . $registration->number);
		echo CHtml::closeTag('tr');

		//headers
		echo CHtml::openTag('tr');
		echo CHtml::tag('td', [
			'rowspan'=>2,
		], '次序<br>Trial');
		echo CHtml::tag('td', [
			'class'=>'signature',
			'rowspan'=>2,
		], '打乱员<br>Scrambler');
		echo CHtml::tag('td', [
			'class'=>'time',
			'rowspan'=>2,
		], '显示时间<br>Display Time');
		if ($event === '333mbf') {
			$penalties = [2, 3, 5, 6];
		} else {
			$penalties = [1, 2, 3, 4];
		}
		foreach ($penalties as $penalty) {
			echo CHtml::openTag('td', [
				'class'=>'penalty',
			]);
			echo CHtml::image($imageDir . "/{$penalty}.png", '', [
				'width'=>45
			]);
			echo CHtml::closeTag('td');
		}
		echo CHtml::tag('td', [
			'class'=>'time',
			'rowspan'=>2,
		], '最终时间<br>Final Time');
		echo CHtml::tag('td', [
			'class'=>'signature',
			'rowspan'=>2,
		], '裁判员<br>Judge');
		echo CHtml::tag('td', [
			'class'=>'bdr signature',
			'rowspan'=>2,
		], '选手核实<br>Competitor');
		echo CHtml::closeTag('tr');

		echo CHtml::openTag('tr');
		if ($event === '333mbf') {
			echo CHtml::tag('td', [], '复原个数<br>Solved');
			echo CHtml::tag('td', [], '尝试个数<br>Attempt');
		}
		if ($event === '333mbf') {
			$penalties = [7, 8];
		} else {
			$penalties = [5, 6, 7, 8];
		}
		foreach ($penalties as $penalty) {
			echo CHtml::openTag('td', [
				'class'=>'penalty',
			]);
			echo CHtml::image($imageDir . "/{$penalty}.png", '', [
				'width'=>45
			]);
			echo CHtml::closeTag('td');
		}
		echo CHtml::closeTag('tr');

		//trials
		$start = $scoreCard['start'] ?? 0;
		$attempt = $scoreCard['attempt'] ?? $attempt;
		for ($i = $start; $i < $attempt; $i++) {
			echo CHtml::openTag('tr');
			echo CHtml::tag('td', [
				'class'=>'trial-no'
			], $i + 1);
			echo CHtml::tag('td', [], '');
			echo CHtml::tag('td', [], self::TIME_SLOT);
			if ($event === '333mbf') {
				echo CHtml::tag('td', [
					'colspan'=>2,
				], '<b>/</b>');
				echo CHtml::tag('td', [
					'colspan'=>2,
				]);
			} else {
				echo CHtml::tag('td', [
					'colspan'=>4,
				]);
			}
			$class = 'bd2';
			if ($i == $start) {
				$class .= ' bd2-top';
			}
			if ($i == $attempt - 1) {
				$class .= ' bd2-bottom';
			}
			echo CHtml::tag('td', [
				'class'=>$class
			], self::TIME_SLOT);
			echo CHtml::tag('td', [], '');
			echo CHtml::tag('td', [
				'class'=>'bdr'
			]);
			echo CHtml::closeTag('tr');
			if ($format == '2/a' && $i == 1 || $format == '1/m' && $i == 0) {
				//cutoff
				echo CHtml::openTag('tr');
				// echo CHtml::tag('td', [
				// 	'class'=>'cutoff',
				// 	'colspan'=>7,
				// ], '');
				echo CHtml::tag('td', [
					'colspan'=>10,
					'class'=>'cutoff',
				], sprintf('%s <span style="font-family:dejavusans">&#9986;</span> %s %s',
					str_pad('', 134, '- '),
					Results::formatTime(($round->cut_off ?? 0) * 100, $event),
					str_pad('', 56, '- ')
				));
				// echo CHtml::tag('td', [
				// 	'class'=>'cutoff',
				// 	'colspan'=>2,
				// ], '');
				echo CHtml::closeTag('tr');
			}
		}

		//remark
		echo CHtml::openTag('tr');
		echo CHtml::tag('td', [
			'class'=>'tal remark',
			'colspan'=>7,
		], '备注 Remark:');
		$timeLimit = Results::formatTime(($round->time_limit ?? 0) * 100, $event);
		if ($timeLimit) {
			$timeLimit = '<' . $timeLimit;
			if (isset($round->cumulative) && $round->cumulative) {
				$timeLimit = '累计' . $timeLimit;
			}
		}
		echo CHtml::tag('td', [
		], $timeLimit);
		echo CHtml::tag('td', [
			'class'=>'bdr',
			'colspan'=>2,
		], '桌号 : 　　');
		echo CHtml::closeTag('tr');

		echo CHtml::closeTag('tbody');
		echo CHtml::closeTag('table');
		$table = ob_get_clean();
		$pdf->WriteHTML($table, 2);
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
		$model->setScenario('register');
		if (isset($_POST['Registration'])) {
			$model->attributes = $_POST['Registration'];
			$model->avatar_type = isset($_POST['Registration']['avatar_type']) ? $_POST['Registration']['avatar_type'] : 0;
			if ($model->competition->require_avatar && $model->avatar_type == Registration::AVATAR_TYPE_NOW) {
				$model->avatar_id = $model->user->avatar_id;
			}
			if ($model->save()) {
				$model->updateEvents($model->events);
				Yii::app()->user->setFlash('success', '更新报名信息成功');
				$this->redirect(array('/board/registration/index', 'Registration'=>array(
					'competition_id'=>$model->competition_id,
				)));
			}
		}
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionCancel() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (!$model->isCancellable()) {
			Yii::app()->user->setFlash('danger', '该选手不能退赛');
			$this->redirect(['/board/registration/index', 'Registration'=>['competition_id'=>$model->competition_id]]);
		}
		if (isset($_POST['cancel'])) {
			if ($model->cancel()) {
				Yii::app()->user->setFlash('success', '选手退赛成功');
				$this->redirect(['/board/registration/index', 'Registration'=>['competition_id'=>$model->competition_id]]);
			}
		}
		$this->render('cancel', array(
			'model'=>$model,
		));
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
		if ($this->user->role != User::ROLE_ADMINISTRATOR && $attribute == 'status' && $competition->isWCACompetition() && $model->user->country_id == 1) {
			throw new CHttpException(401, '大陆选手请通过粗饼在线支付完成报名，如特殊情况请联系管理员');
		}
		if ($model->isCancelled()) {
			throw new CHttpException(401, '已退赛选手不做任何变更');
		}
		$model->$attribute = 1 - $model->$attribute;
		if ($attribute == 'signed_in') {
			if ($model->signed_in) {
				$model->signed_date = time();
				$auth = ScanAuth::getCompetitionAuth($competition);
				$model->signed_scan_code = $auth->code;
			} else {
				$model->signed_date = 0;
			}
		}
		//前面改过status了，所以此处是isAccepted
		if ($attribute == 'status' && $model->isAccepted()) {
			$model->total_fee = $model->getTotalFee(true);
			$model->$attribute = 1 - $model->$attribute;
			$model->accept();
		} else {
			$model->save();
		}
		$this->ajaxOk(array(
			'value'=>$model->$attribute,
		));
	}

	public function actionAcceptNewcomer() {
		$id = $this->iRequest('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'Not found');
		}
		$competition = $model->competition;
		if ($competition === null) {
			throw new CHttpException(404, 'Not found');
		}
		if ($this->user->isOrganizer()) {
			throw new CHttpException(401, 'Unauthorized');
		}
		if ($this->user->role != User::ROLE_ADMINISTRATOR && !$competition->canRegister()) {
			throw new CHttpException(400, '报名已截止，如需变更请联系代表或管理员');
		}
		if ($model->isCancelled()) {
			throw new CHttpException(400, '已退赛选手不做任何变更');
		}
		// check status
		if (!$model->isWaiting()) {
			throw new CHttpException(400, '仅能通过已完成报名流程的选手');
		}
		// check for top 50% newcomer
		$user = $model->user;
		$currentYear = date('Y', $competition->date);
		$registrations = Registration::model()->with('user')->findAllByAttributes([
			'competition_id'=>$competition->id,
			'status'=>[Registration::STATUS_ACCEPTED, Registration::STATUS_WAITING],
		]);
		$waitingRegistrations = array_filter($registrations, function($registration) use ($model) {
			return $registration->isWaiting();
		});
		$waitingNewcomers = array_filter($waitingRegistrations, function($registration) use ($currentYear) {
			return !$registration->user->wcaid || substr($registration->user->wcaid, 0, 4) == $currentYear;
		});
		$acceptedNewcomers = array_filter($registrations, function($registration) use ($currentYear) {
			if (!$registration->isAccepted()) {
				return false;
			}
			return !$registration->user->wcaid || substr($registration->user->wcaid, 0, 4) == $currentYear;
		});
		$reached50Percent = count($acceptedNewcomers) >= $competition->person_num / 2;
		$oneDayBeforeCancellationEnd = $competition->cancellation_end_time - 86400;
		$now = time();
		// if one of the following conditions is met, then accept by the order of registration
		// 1. newcomers reached 50% of competitor limit
		// 2. one day before cancellation end
		// 3. registration ended and no waiting newcomers
		// @todo how to handle this case: 100 limit, 200 registered but only 40 newcomers
		if ($reached50Percent ||
			($now > $oneDayBeforeCancellationEnd && $now < $competition->reg_reopen_time && count($waitingNewcomers) == 0) ||
			($now > $competition->reg_end && count($waitingNewcomers) == 0)
		) {
			$priorRegistrations = array_filter($waitingRegistrations, function($registration) use ($model) {
				return $registration->accept_time < $model->accept_time || ($registration->accept_time == $model->accept_time && $registration->id < $model->id);
			});
			if ($priorRegistrations != []) {
				throw new CHttpException(400, "请先通过更早完成报名流程的选手\n" . implode("\n", array_map(function($registration) {
						return $registration->user->getCompetitionName() . ' ' . $registration->user->wcaid;
					}, $priorRegistrations)));
			}
		} else {
			// only accept newcomer by the order of registration
			if ($user->wcaid && substr($user->wcaid, 0, 4) != $currentYear) {
				throw new CHttpException(400, '请优先通过新人选手');
			} else {
				$priorRegistrations = array_filter($waitingNewcomers, function($registration) use ($model) {
					return $registration->accept_time < $model->accept_time || ($registration->accept_time == $model->accept_time && $registration->id < $model->id);
				});
				if ($priorRegistrations != []) {
					throw new CHttpException(400, "请先通过更早完成报名流程的选手\n" . implode("\n", array_map(function($registration) {
						return $registration->user->getCompetitionName() . ' ' . $registration->user->wcaid;
					}, $priorRegistrations)));
				}
			}
		}
		$model->acceptForNewcomer();
		$this->ajaxOk(array(
			'value'=>$model->status,
		));
	}
}
