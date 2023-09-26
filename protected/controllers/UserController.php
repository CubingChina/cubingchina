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

	public function actionBind() {
		$user = $this->user;
		$webUser = Yii::app()->user;
		$sessionWechatUser = Yii::app()->session->get(Constant::WECHAT_SESSION_KEY);
		if ($this->isInWechat) {
			$action = $_POST['action'] ?? '';
			switch ($action) {
				case 'bind':
					if ($user->wechatUser === null) {
						$wechatUser = WechatUser::getOrCreate($sessionWechatUser);
						if ($wechatUser->user === null) {
							$wechatUser->user_id = $user->id;
							$wechatUser->save();
							$webUser->setFlash('success', Yii::t('User', 'Bind successfully.'));
							$this->redirect(['/user/bind']);
						} else {
							$webUser->setFlash('danger', Yii::t('User', 'Current Wechat user has been bound to another user.'));
						}
					} else {
						$webUser->setFlash('danger', Yii::t('User', 'You already bound an account.'));
					}
					break;
				case 'unbind':
					if ($user->wechatUser !== null) {
						$user->wechatUser->user_id = 0;
						$user->wechatUser->save();
						$webUser->setFlash('success', Yii::t('User', 'Unbind successfully.'));
						$this->redirect(['/user/bind']);
					} else {
						$webUser->setFlash('danger', Yii::t('User', 'You haven\'t bound an account.'));
					}
					break;
			}
		}
		$this->render('bind', [
			'user'=>$user,
			'sessionWechatUser'=>$sessionWechatUser,
		]);
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

	public function actionPreferredEvents() {
		$user = $this->getUser();
		if (isset($_POST['User'])) {
			$user->attributes = $_POST['User'];
			PreferredEvent::updateUserEvents($user);
			if (PreferredEvent::updateUserEvents($user)) {
				Yii::app()->user->setFlash('success', Yii::t('common', 'Your preferred events have been updated successfully.'));
				$this->redirect(array('/user/preferredEvents'));
			}
		}
		$this->render('preferredEvents', array(
			'user'=>$user,
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
			$dirname = 'upload/' . $md5[0] . '/';
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
