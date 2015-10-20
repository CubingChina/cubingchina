<?php
class ReviewController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'permission'=>'review',
				),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionAdd() {
		$model = new Review();
		$model->unsetAttributes();
		$model->user_id = $this->user->id;
		$model->date = time();
		if (isset($_POST['Review'])) {
			$model->attributes = $_POST['Review'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加评价成功');
				$this->redirect(array('/board/review/index'));
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Review::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['Review'])) {
			$model->attributes = $_POST['Review'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新评价成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionIndex() {
		$model = new Review();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Review');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionUsers() {
		$allUsers = User::model()->findAllByAttributes(array(
			'status'=>User::STATUS_NORMAL,
		));
		$datum = array_map(function($user) {
			return array(
				'full'=>$user->getCompetitionName() . ' ' . $user->id,
				'value'=>$user->id . '-' . $user->name_zh,
				'label'=>$user->name_zh,
			);
		}, $allUsers);
		$users = CHtml::listData($allUsers, 'id', 'name_zh');
		$this->ajaxOk(array(
			'datum'=>$datum,
			'users'=>$users,
		));
	}
}
