<?php
class UserController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(User::ROLE_ADMINISTRATOR),
			),
			array(
				'allow',
				'roles'=>array(User::ROLE_ORGANIZER),
				'actions'=>array('statistics'),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$model = new User('search');
		$model->unsetAttributes();
		$model->status = array(
			User::STATUS_NORMAL,
			User::STATUS_BANNED,
		);
		$model->attributes = $this->aRequest('User');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionStatistics() {
		$totalUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
		));
		$advancedUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
			'role'=>array(
				User::ROLE_DELEGATE,
				User::ROLE_ORGANIZER,
				User::ROLE_ADMINISTRATOR,
			),
		));
		$uncheckedUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
			'role'=>array(
				User::ROLE_UNCHECKED,
			),
		));
		$userPerDay = round($totalUser / ceil((time() - strtotime('2014-06-06')) / 86400), 2);
		$totalRegistration = Registration::model()->with('user')->count('user.status=' . User::STATUS_NORMAL);
		$acceptedRegistration = Registration::model()->with('user')->countByAttributes(array(
			'status'=>Registration::STATUS_ACCEPTED,
		), 'user.status=' . User::STATUS_NORMAL);
		$dailyUser = User::getDailyUser();
		$hourlyUser = User::getHourlyUser();
		$hourlyRegistration = Registration::getHourlyRegistration();
		$hourlyData = $this->mergeHourlyData($hourlyUser, $hourlyRegistration);
		$userRegion = User::getUserRegion();
		$userGender = User::getUserGender();
		$userAge = User::getUserAge();
		$this->render('statistics', array(
			'totalUser'=>$totalUser,
			'advancedUser'=>$advancedUser,
			'uncheckedUser'=>$uncheckedUser,
			'userPerDay'=>$userPerDay,
			'totalRegistration'=>$totalRegistration,
			'acceptedRegistration'=>$acceptedRegistration,
			'dailyUser'=>$dailyUser,
			'hourlyData'=>$hourlyData,
			'userRegion'=>$userRegion,
			'userGender'=>$userGender,
			'userAge'=>$userAge,
		));
	}

	private function mergeHourlyData() {
		$data = func_get_args();
		$keys = array();
		foreach ($data as $key=>$value) {
			if ($value === array()) {
				unset($data[$key]);
				continue;
			}
			$data[$key] = $this->fillHourlyData($value);
		}
		$hourlyData = array_fill(0, 24, array());
		foreach ($data as $key=>$value) {
			array_walk($value, function($value, $key) use (&$hourlyData) {
				$hourlyData[$key] = array_merge($hourlyData[$key], $value);
			});
		}
		return $hourlyData;
	}

	private function fillHourlyData($data) {
		$keys = array_keys($data[0]);
		$key = $keys[1];
		$temp = $data[0];
		$temp[$key] = 0;
		$tempData = array();
		foreach ($data as $key=>$value) {
			$tempData[$value['hour']] = $value;
		}
		for ($i = 0; $i < 24; $i++) {
			if (!isset($tempData[$i])) {
				$temp['hour'] = $i;
				$tempData[$i] = $temp;
			}
		}
		ksort($tempData);
		return array_values($tempData);
	}

	public function actionAdd() {
		$model = new User();
		$model->birthday = '';
		$model->province_id = $model->city_id = '';
		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];
			$model->handleDate();
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加用户成功');
				$this->redirect(array('/board/user/index'));
			}
		}
		$model->formatDate();
		$roles = User::getRoles();
		$genders = User::getGenders();
		$cities = Region::getAllCities();
		$this->render('edit', array(
			'model'=>$model,
			'roles'=>$roles,
			'genders'=>$genders,
			'cities'=>$cities,
		));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];
			$model->handleDate();
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '编辑用户成功');
				$this->redirect(array('/board/user/index'));
			}
		}
		$model->formatDate();
		$roles = User::getRoles();
		$genders = User::getGenders();
		$cities = Region::getAllCities();
		$this->render('edit', array(
			'model'=>$model,
			'roles'=>$roles,
			'genders'=>$genders,
			'cities'=>$cities,
		));
	}

	public function actionDisable() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_BANNED;
		$model->save();
		Yii::app()->user->setFlash('success', '拉黑用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionEnable() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_NORMAL;
		$model->save();
		Yii::app()->user->setFlash('success', '洗白用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionDelete() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_DELETED;
		$model->save();
		Yii::app()->user->setFlash('success', '删除用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
