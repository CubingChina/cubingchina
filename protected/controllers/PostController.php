<?php

class PostController extends Controller {
	public function actionDetail() {
		$alias = $this->sGet('alias');
		$news = News::model()->findByAttributes([
			'alias'=>$alias,
			'status'=>News::STATUS_SHOW,
		]);
		$this->title = $news->getAttributeValue('title');
		$this->pageTitle = [$this->title];
		$this->breadcrumbs = [
			$this->title,
		];
		if ($news === null) {
			throw new CHttpException(404, 'Error');
		}
		$this->render('detail', [
			'news'=>$news
		]);
	}

	public function accessRules() {
		return [
			[
				'allow',
				'users'=>['*'],
			],
		];
	}
}
