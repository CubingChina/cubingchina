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
}
