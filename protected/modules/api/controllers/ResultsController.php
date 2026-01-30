<?php

class ResultsController extends ApiController {
	public function actionPersonMap() {
		$id = strtoupper($this->sGet('id'));
		$person = Persons::model()->with('country')->findByAttributes(['wca_id' => $id, 'sub_id'=>1]);
		if ($person == null) {
			$this->ajaxError(Constant::ERROR_NOT_FOUND);
		}
		$data = Yii::app()->cache->getData(['Persons', 'getResults'], $id);
		extract($data);
		$regions = require BASE_PATH . '/messages/zh_cn/Region.php';
		$this->ajaxOK([
			'worlds'=>[
				'center'=>$mapCenter,
				'data'=>array_map(function($data) {
					return [
						'name'=>ActiveRecord::getModelAttributeValue($data, 'name'),
						'city_name'=>ActiveRecord::getModelAttributeValue($data, 'city_name'),
						'date'=>$data['date'],
						'longitude'=>$data['longitude'],
						'latitude'=>$data['latitude'],
					];
				}, $mapData),
			],
			'provinces'=>array_map(function($data) use ($regions) {
				$name = $data['name_zh'];
				if (isset($regions[$name])) {
					$name = $regions[$name];
				}
				return [
					'name'=>$name,
					'province'=>Yii::t('Region', ActiveRecord::getModelAttributeValue($data, 'name')),
					'value'=>$data['count'],
				];
			}, $visitedProvinces),
		]);
	}
}
