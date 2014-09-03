<?php

class UploadController extends AdminController {
	public function actionImage() {
		$params = Yii::app()->params;
		$file = CUploadedFile::getInstanceByName('imgFile');
		if ($file === null || $file->getHasError()) {
			$this->jsonReturn($file === null ? 1 : $file->getError(), '上传失败，请联系管理员');
		}
		$imagesize = getimagesize($file->getTempName());
		if ($imagesize === false) {
			$this->jsonReturn(1, '请上传正确格式的图片');
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
			$url = $params->staticUrlPrefix . $dirname . $filename;
			$this->jsonReturn(0, '', $url);
		} else {
			$this->jsonReturn(1, '上传失败，请联系管理员');
		}
	}

	private function jsonReturn($error, $message = '', $url = '') {
		$this->setIsAjaxRequest(true);
		header('Content-type: text/html; charset=UTF-8');
		echo CJSON::encode(array(
			'error' => $error,
			'message' => $message,
			'url' => $url,
		));
		Yii::app()->end();
	}
}