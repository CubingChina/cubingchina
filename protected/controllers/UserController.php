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

	public function actionCert() {
		$competitions = Competition::model()->cache(86400)->findAllByAttributes([
			'type'=>Competition::TYPE_WCA,
			'status'=>Competition::STATUS_SHOW,
		], [
			'condition'=>'cert_name!=""',
		]);
		$competitions = array_filter($competitions, function($competition) {
			return $competition->hasUserResults($this->user->wcaid);
		});
		$this->render('cert', array(
			'competitions'=>$competitions,
		));
	}

	public function actionCompetitionHistory() {
		if ($this->user->id === '') {
			$this->redirect(array('/user/competitions'));
		}
		$model = new Competitions('search');
		$model->unsetAttributes();
		$this->render('competitionHistory', array(
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
			'avatar'=>Yii::app()->params['avatar'],
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

	public function actionUpload() {
		$params = Yii::app()->params;
		$file = CUploadedFile::getInstanceByName('avatar');
		try {
			if ($file === null) {
				throw new Exception(Yii::t('common', 'No file uploaded'), 1);
			}
			if ($file->getHasError()) {
				throw new Exception(Yii::t('common', 'Upload failed, please contact the administrator'), $file->getError());
			}
			$imagesize = getimagesize($file->getTempName());
			if ($imagesize === false) {
				throw new Exception(Yii::t('common', 'Invalid file type of image'), 2);
			}
			if ($imagesize[0] > $params['avatar']['width'] || $imagesize[1] > $params['avatar']['height']) {
				throw new Exception(Yii::t('common', 'Image height or width exceeded, the limited width is {width} and height is {height}', array(
					'{width}'=>$params['avatar']['width'],
					'{height}'=>$params['avatar']['height'],
				)), 3);
			}
			if (filesize($file->getTempName()) > $params['avatar']['size']) {
				throw new Exception(Yii::t('common', 'File is too large, the limited size is {size}', array(
					'{size}'=>sprintf('%.2fMB', $params['avatar']['size'] / 1048576),
				)), 4);
			}
			$basePath = $params->staticPath;
			$extension = image_type_to_extension($imagesize[2]);
			$md5 = md5(file_get_contents($file->getTempName()));
			$filename = $md5 . $extension;
			$dirname = 'upload/' . $md5{0} . '/';
			$fullPath = $params->staticPath . $dirname . $filename;
			$fullDir = dirname($fullPath);
			if (!is_dir($fullDir)) {
				mkdir($fullDir, 0755, true);
			}
			if (file_exists($fullPath) || $file->saveAs($fullPath)) {
				$userAvatar = new UserAvatar();
				$userAvatar->user_id = $this->user->id;
				$userAvatar->md5 = $md5;
				$userAvatar->extension = $extension;
				$userAvatar->width = $imagesize[0];
				$userAvatar->height = $imagesize[1];
				$userAvatar->add_time = time();
				$userAvatar->save(false);
				$this->user->avatar_id = $userAvatar->id;
				$this->user->save();
				$url = $params->staticUrlPrefix . $dirname . $filename;
				$errorCode = 0;
				$errorMsg = '';
			} else {
				throw new Exception(Yii::t('common', 'Upload failed, please contact the administrator'), 1);
			}
		} catch (Exception $e) {
			$url = '';
			$errorCode = $e->getCode();
			$errorMsg = $e->getMessage();
		}
		$this->render('upload', array(
			'url'=>$url,
			'errorCode'=>$errorCode,
			'errorMsg'=>$errorMsg,
		));
	}
}
