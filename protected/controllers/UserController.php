<?php
class UserController extends Controller {
	public $defaultAction = 'profile';

	public function actionCompetitions() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->user_id = $this->user->id;
		$this->render('competitions', array(
			'model'=>$model,
		));
	}

	public function actionPassword() {
		$this->render('password');
	}

	public function actionProfile() {
		$user = $this->getUser();
		$this->render('profile', array(
			'user'=>$user,
		));
	}

	public function actionEdit() {
		$user = $this->getUser();
		$model = new EditProfileForm();
		$model->attributes = $user->attributes;
		if (isset($_POST['EditProfileForm'])) {
			$model->attributes = $_POST['EditProfileForm'];
			if ($model->validate() && $model->update()) {
				Yii::app()->user->setFlash('success', Yii::t('common', 'Your profile has been updated successfully.'));
				$this->redirect(array('/user/profile'));
			}
		}
		$this->render('edit', array(
			'user'=>$user,
			'model'=>$model,
		));
	}
}
