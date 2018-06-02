<?php

class ResultsController extends ApiController {
	public function actionPersonMap() {
		$id = strtoupper($this->sGet('id'));
		$person = Persons::model()->with('country')->findByAttributes(['id' => $id]);
		if ($person == null) {
			$this->ajaxError(Constant::ERROR_NOT_FOUND);
		}
		$data = Yii::app()->cache->getData(['Persons', 'getResults'], $id);
		extract($data);
		$this->ajaxOK([
			'worlds'=>[
				'center'=>$mapCenter,
				'data'=>array_map(function($data) {
					$data['name'] = ActiveRecord::getModelAttributeValue($data, 'name');
					$data['city_name'] = ActiveRecord::getModelAttributeValue($data, 'city_name');
					return $data;
				}, $mapData),
			]
		]);
	}
}
