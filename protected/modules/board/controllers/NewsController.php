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
			throw $e;
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
			$temp = $$top3 = new stdClass();
			$result = $results[$key];
			$temp->name = $result->personName;
			$temp->name_zh = preg_match('{\((.*?)\)}i', $result->personName, $matches) ? $matches[1] : $result->personName;
			$temp->link = CHtml::link($temp->name, 'https://www.worldcubeassociation.org/results/p.php?i=' . $result->personId, array('target'=>'_blank'));
			$temp->link_zh = CHtml::link($temp->name_zh, 'https://www.worldcubeassociation.org/results/p.php?i=' . $result->personId, array('target'=>'_blank'));
			$temp->score = Results::formatTime($result->average, $result->eventId);
			$temp->score_zh = Results::formatTime($result->average, $result->eventId);
			if ($top3 === 'winner') {
				switch ($eventId) {
					case '333fm':
						$temp->score .= ' turns';
						$temp->score_zh .= '步';
						break;
					default:
						if (is_numeric($temp->score)) {
							$temp->score .= ' seconds';
							$temp->score_zh .= '秒';
						}
						break;
				}
			}
			$data[$top3] = $$top3;
		}
		$data['records'] = '';
		$data['records_zh'] = '';
		return $data;
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
