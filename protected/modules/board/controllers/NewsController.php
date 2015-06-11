<?php
class NewsController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(User::ROLE_ADMINISTRATOR),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionAdd() {
		$model = new News();
		$model->user_id = $this->user->id;
		$model->date = time();
		$model->status = News::STATUS_HIDE;
		// $model->unsetAttributes();
		if (isset($_POST['News'])) {
			$model->attributes = $_POST['News'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加新闻成功');
				$this->redirect(array('/board/news/index'));
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['News'])) {
			$model->attributes = $_POST['News'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新新闻成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionIndex() {
		$model = new News();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('News');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionEditTemplate() {
		$id = $this->iGet('id');
		$model = NewsTemplate::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['NewsTemplate'])) {
			$model->attributes = $_POST['NewsTemplate'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新新闻模板成功');
				$this->redirect($this->getReferrer());
			}
		}
		$this->render('editTemplate', array(
			'model'=>$model,
		));
	}

	public function actionTemplate() {
		$model = new NewsTemplate();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('NewsTemplate');
		$this->render('template', array(
			'model'=>$model,
		));
	}

	public function actionRender() {
		$competition = Competition::model()->findByPk($this->iRequest('competition_id'));
		$template = NewsTemplate::model()->findByPk($this->iRequest('template_id'));
		if ($competition === null || $template === null) {
			$this->ajaxOK(null);
		}
		$attributes = $template->attributes;
		$data = $this->generateTemplateData($competition);
		set_error_handler(function($errno, $errstr) {
			throw new Exception($errstr);
		});
		try {
			foreach ($attributes as $key=>$attribute) {
				$attributes[$key] = preg_replace_callback('|\{([^}]+)\}|i', function($matches) use($data) {
					$result = $this->evaluateExpression($matches[1], $data);
					if (is_array($result)) {
						$result = CHtml::normalizeUrl($result);
					}
					return $result;
				}, $attribute);
			}
		} catch (Exception $e) {
			$this->ajaxOK(null);
		}
		$this->ajaxOK($attributes);
	}

	private function generateTemplateData($competition) {
		$data = array(
			'competition'=>$competition,
		);
		if ($competition->wca_competition_id == '') {
			return $data;
		}
		$events = CHtml::listData(Results::model()->findAllByAttributes(array(
			'competitionId'=>$competition->wca_competition_id,
		), array(
			'group'=>'eventId',
			'select'=>'eventId,COUNT(1) AS average'
		)), 'eventId', 'average');
		if ($events === array()) {
			return $data;
		}
		arsort($events);
		$eventId = array_keys($events)[0];
		$primaryEvents = array(
			'333',
			'777',
			'666',
			'555',
			'444',
			'222',
			'333fm',
			'333oh',
			'333ft',
			'333bf',
			'444bf',
			'555bf',
		);
		foreach ($primaryEvents as $event) {
			if (isset($events[$event])) {
				$eventId = $event;
				break;
			}
		}
		$results = Results::model()->findAllByAttributes(array(
			'competitionId'=>$competition->wca_competition_id,
			'roundId'=>array(
				'c',
				'f',
			),
			'eventId'=>$eventId,
			'pos'=>array(1, 2, 3),
		), array(
			'order'=>'eventId, pos',
		));
		if (count($results) < 3) {
			return $data;
		}
		$event = new stdClass();
		$event->name = Events::getFullEventName($eventId);
		$event->name_zh = Yii::t('event', $event->name);
		$data['event'] = $event;
		$winners = array('winner', 'runnerUp', 'secondRunnerUp');
		foreach ($winners as $key=>$top3) {
			$data[$top3] = $this->makePerson($results[$key]);
		}
		$data['records'] = array();
		$data['records_zh'] = array();
		$recordResults = Results::model()->findAllByAttributes(array(
			'competitionId'=>$competition->wca_competition_id,
		), array(
			'condition'=>'regionalSingleRecord !="" OR regionalAverageRecord !=""',
			'order'=>'best, average',
		));
		$records = array();
		foreach ($recordResults as $record) {
			if ($record->regionalSingleRecord) {
				$records[$record->regionalSingleRecord]['single'][] = $record;
			}
			if ($record->regionalAverageRecord) {
				$records[$record->regionalAverageRecord]['average'][] = $record;
			}
		}
		foreach ($records as $region=>$record) {
			if (isset($record['single'])) {
				$records[$region]['single'] = $this->filterRecords($record['single'], 'best', $region);
			}
			if (isset($record['average'])) {
				$records[$region]['average'] = $this->filterRecords($record['average'], 'average', $region);
			}
		}
		if (isset($records['WR'])) {
			$rec = $this->makeRecords($records['WR']);
			$data['records'][] = sprintf('World records: %s.', $rec['en']);
			$data['records_zh'][] = sprintf('世界纪录：%s。', $rec['zh']);
		}
		$continents = array(
			'AfR'=>array(
				'en'=>'Africa',
				'zh'=>'非洲',
			),
			'AsR'=>array(
				'en'=>'Asia',
				'zh'=>'亚洲',
			),
			'OcR'=>array(
				'en'=>'Oceania',
				'zh'=>'大洋洲',
			),
			'ER'=>array(
				'en'=>'Europe',
				'zh'=>'欧洲',
			),
			'NAR'=>array(
				'en'=>'North America',
				'zh'=>'北美洲',
			),
			'SAR'=>array(
				'en'=>'South America',
				'zh'=>'南美洲',
			),
		);
		foreach ($continents as $cr=>$continent) {
			if (isset($records[$cr])) {
				$rec = $this->makeRecords($records[$cr]);
				$data['records'][] = sprintf('%s records: %s.', $continent['en'], $rec['en']);
				$data['records_zh'][] = sprintf('%s纪录：%s。', $continent['zh'], $rec['zh']);
			}
		}
		if (isset($records['NR'])) {
			$rec = $this->makeRecords($records['NR'], true);
			foreach ($rec['en'] as $country=>$re) {
				$re = implode(', ', $re);
				$data['records'][] =sprintf('%s records: %s.', $country, $re);
			}
			foreach ($rec['zh'] as $country=>$re) {
				$re = implode('；', $re);
				switch ($country) {
					case 'China':
						$country = '中国';
						break;
					case 'Hong Kong':
						$country = '香港';
						break;
					case 'Macau':
						$country = '澳门';
						break;
					case 'Taiwan':
						$country = '台湾';
						break;
				}
				$data['records_zh'][] =sprintf('%s纪录：%s。', $country, $re);
			}
		}
		$data['records'] = implode('<br>', $data['records']);
		$data['records_zh'] = implode('<br>', $data['records_zh']);
		if (!empty($data['records'])) {
			$data['records'] = '<br>' . $data['records'];
			$data['records_zh'] = '<br>' . $data['records_zh'];
		}
		return $data;
	}

	private function filterRecords($records, $attribute, $region) {
		usort($records, function($recordA, $recordB) use($attribute) {
			return $recordA->$attribute - $recordB->$attribute;
		});
		$temp = array();
		$region = strtoupper($region);
		foreach ($records as $record) {
			if ($region !== 'NR') {
				if (!isset($temp[$record->eventId])) {
					$temp[$record->eventId] = $record;
				}
			} else {
				if (!isset($temp[$record->personCountryId][$record->eventId])) {
					$temp[$record->personCountryId][$record->eventId] = $record;
				}
			}
		}
		if ($region === 'NR') {
			$temp = call_user_func_array('array_merge', array_map('array_values', $temp));
		}
		return $temp;
	}

	private function makePerson($result, $appendUnit = true, $type = 'both') {
		switch ($type) {
			case 'average':
				$score = $result->average;
				break;
			case 'single':
				$score = $result->best;
				break;
			default:
				$score = $result->average ?: $result->best;
				break;
		}
		$temp = new stdClass();
		$temp->name = $result->personName;
		$temp->name_zh = preg_match('{\((.*?)\)}i', $result->personName, $matches) ? $matches[1] : $result->personName;
		$temp->link = CHtml::link($temp->name, 'https://www.worldcubeassociation.org/results/p.php?i=' . $result->personId, array('target'=>'_blank'));
		$temp->link_zh = CHtml::link($temp->name_zh, 'https://www.worldcubeassociation.org/results/p.php?i=' . $result->personId, array('target'=>'_blank'));
		$temp->score = Results::formatTime($score, $result->eventId);
		$temp->score_zh = $temp->score;
		if ($appendUnit && is_numeric($temp->score)) {
			switch ($result->eventId) {
				case '333fm':
					$unit = array(
						'en'=>' turns',
						'zh'=>'步',
					);
					break;
				default:
					$unit = array(
						'en'=>' seconds',
						'zh'=>'秒',
					);
					break;
			}
			$temp->score .= $unit['en'];
			$temp->score_zh .= $unit['zh'];
		}
		return $temp;
	}

	private function makeRecords($records, $isNR = false) {
		$rec = array(
			'en'=>array(),
			'zh'=>array(),
		);
		foreach ($records as $type=>$recs) {
			foreach ($recs as $result) {
				$eventName = Events::getFullEventName($result->eventId);
				$temp = $this->makePerson($result, true, $type);
				$enRec = sprintf('%s %s %s (%s)',
					$temp->link,
					$eventName,
					$temp->score,
					$type
				);
				$zhRec = sprintf('%s的%s纪录（%s），创造者%s',
					Yii::t('event', $eventName),
					$type === 'average' ? '平均' : '单次',
					$temp->score_zh,
					$temp->link_zh
				);
				if ($isNR) {
					$rec['en'][$result->personCountryId][] = $enRec;
					$rec['zh'][$result->personCountryId][] = $zhRec;
				} else {
					$rec['en'][] = $enRec;
					$rec['zh'][] = $zhRec;
				}
			}
		}
		if (!$isNR) {
			$rec['en'] = implode(', ', $rec['en']);
			$rec['zh'] = implode('；', $rec['zh']);
		}
		return $rec;
	}

	public function actionShow() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = News::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '发布新闻成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = News::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏新闻成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
