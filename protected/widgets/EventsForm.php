<?php

class EventsForm extends Widget {
	public $model;
	public $competition;
	public $name = 'events';
	public $events = [];
	public $unmetEvents = [];
	public $shouldDisableUnmetEvents = false;
	public $type = 'checkbox';
	public $htmlOptions = [];
	public $labelOptions = [];
	public $numberOptions = [];
	public $feeOptions = [];
	public function run() {
		$events = $this->events;
		foreach ($events as $key=>$value) {
			$events[$key] = Yii::t('event', $value);
		}
		$model = $this->model;
		$name = $this->name;
		$type = $this->type;
		$competition = $this->competition;
		$htmlOptions = $this->htmlOptions;
		$labelOptions = $this->labelOptions;
		$numberOptions = $this->numberOptions;
		$feeOptions = $this->feeOptions;
		if ($this->type == 'checkbox') {
			echo CHtml::openTag('div', $htmlOptions);
			foreach ($events as $event=>$value) {
				$unmet = isset($this->unmetEvents[$event]);
				$disabled = $this->shouldDisableUnmetEvents && $unmet;
				if ($disabled) {
					continue;
				}
				$this->renderEvent($event);
			}
			echo CHtml::error($model, 'events', ['class'=>'text-danger']);
			if ($this->shouldDisableUnmetEvents) {
				$params = [
					'{time}'=>date('Y-m-d H:i:s', $competition->qualifying_end_time) . ' GMT+8',
				];
				if (count($events) == count($this->unmetEvents)) {
					echo CHtml::tag('p', [], Yii::t('Registration', 'To register the following events, you need to meet the qualifying times before {time}. Please come back after you meet any qualifying times.', $params));
				} else {
					echo '<hr>';
					echo CHtml::tag('p', [], Yii::t('Registration', 'To register the following events, you need to meet the qualifying times before {time}. You can add them later after you reach the corresponding qualifying time.', $params));
				}
				foreach ($events as $event=>$value) {
					$unmet = isset($this->unmetEvents[$event]);
					$disabled = $this->shouldDisableUnmetEvents && $unmet;
					if (!$disabled) {
						continue;
					}
					$this->renderEvent($event, true);
				}
			}
			echo CHtml::closeTag('div');
			if ($competition && $competition->isMultiLocation()) {
				echo CHtml::closeTag('div');
				$locations = [];
				$options = [];
				foreach ($competition->sortedLocations as $location) {
					$locations[$location->location_id] = $competition->multi_countries ? $location->getCityName(true, true) : $location->getFullAddress(false);
					$options[$location->location_id] = [
						'data-display-fee'=>$location->getFeeInfo(),
						'data-fee'=>$location->getFeeNumber(),
					];
				}
				echo CHtml::activeLabelEx($model, 'location_id');
				echo CHtml::activeDropDownList($model, 'location_id', $locations, [
					'class'=>'form-control',
					'prompt'=>'',
					'options'=>$options,
				]);
				echo CHtml::error($model, 'location_id', ['class'=>'text-danger']);
				echo CHtml::openTag('div', [
					'class'=>'form-group',
				]);
			}
		} else {
			if (!isset($htmlOptions['class'])) {
				$htmlOptions['class'] = 'row';
			} else {
				$htmlOptions['class'] .= ' row';
			}
			if (!isset($numberOptions['class'])) {
				$numberOptions['class'] = 'form-control';
			} else {
				$numberOptions['class'] .= ' form-control';
			}
			if (!isset($feeOptions['class'])) {
				$feeOptions['class'] = 'form-control';
			} else {
				$feeOptions['class'] .= ' form-control';
			}
			if (!isset($labelOptions['class'])) {
				$labelOptions['class'] = 'text-right col-md-2 col-sm-4';
			} else {
				$labelOptions['class'] .= ' text-right col-md-2 col-sm-4';
			}
			echo CHtml::openTag('div', $htmlOptions);
			foreach ($events as $event=>$value) {
				echo CHtml::openTag('div', [
					'class'=>'col-lg-12',
				]);
				echo CHtml::openTag('div', [
					'class'=>'row',
				]);
				//label
				$labelOptions['label'] = Events::getFullEventName($event) . ': ';
				echo CHtml::activeLabelEx($model, "{$name}[{$event}][round]", $labelOptions);
				//round
				echo CHtml::openTag('div', [
					'class'=>'col-md-2 col-sm-8',
				]);
				echo CHtml::openTag('div', [
					'class'=>'input-group',
				]);
				echo CHtml::activeNumberField($model, "{$name}[{$event}][round]", $numberOptions);
				echo CHtml::tag('span', ['class'=>'input-group-addon'], Yii::t('common', 'Rounds'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');

				//fee
				echo CHtml::openTag('div', [
					'class'=>'col-md-5 row',
				]);
				//normal fee
				echo CHtml::openTag('div', [
					'class'=>'col-xs-4',
				]);
				echo CHtml::openTag('div', [
					'class'=>'input-group row',
				]);
				echo CHtml::activeNumberField($model, "{$name}[{$event}][fee]", $feeOptions);
				echo CHtml::tag('span', ['class'=>'input-group-addon'], Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				//second
				echo CHtml::openTag('div', [
					'class'=>'col-xs-4',
				]);
				echo CHtml::openTag('div', [
					'class'=>'input-group row',
				]);
				echo CHtml::activeNumberField($model, "{$name}[{$event}][fee_second]", $feeOptions);
				echo CHtml::tag('span', ['class'=>'input-group-addon'], Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				//third
				echo CHtml::openTag('div', [
					'class'=>'col-xs-4',
				]);
				echo CHtml::openTag('div', [
					'class'=>'input-group row',
				]);
				echo CHtml::activeNumberField($model, "{$name}[{$event}][fee_third]", $feeOptions);
				echo CHtml::tag('span', ['class'=>'input-group-addon'], Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');

				//qualifying times
				if ($model->has_qualifying_time) {
					echo CHtml::openTag('div', [
						'class'=>'col-md-3 row',
					]);
					//normal fee
					echo CHtml::openTag('div', [
						'class'=>'col-xs-6',
					]);
					echo CHtml::openTag('div', [
						'class'=>'input-group row',
					]);
					echo CHtml::tag('span', ['class'=>'input-group-addon'], '单次');
					echo CHtml::activeNumberField($model, "{$name}[{$event}][qualifying_best]", $feeOptions);
					echo CHtml::closeTag('div');
					echo CHtml::closeTag('div');
					//second
					echo CHtml::openTag('div', [
						'class'=>'col-xs-6',
					]);
					echo CHtml::openTag('div', [
						'class'=>'input-group row',
					]);
					echo CHtml::tag('span', ['class'=>'input-group-addon'], '平均');
					echo CHtml::activeNumberField($model, "{$name}[{$event}][qualifying_average]", $feeOptions);
					echo CHtml::closeTag('div');
					echo CHtml::closeTag('div');
					echo CHtml::closeTag('div');
				}

				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
			}
			echo CHtml::closeTag('div');
		}
	}

	private function renderEvent($event, $disabled = false) {
		$text = Events::getFullEventNameWithIcon($event);
		echo CHtml::openTag('div', [
			'class'=>'checkbox checkbox-inline' . ($disabled ? ' disabled' : ''),
		]);
		echo CHtml::openTag('label', [
			'class'=>$disabled ? 'bg-danger' : '',
		]);
		$options = [
			'id'=>'Registration_events_' . $event,
			'class'=>'registration-events',
			'value'=>$event,
			'disabled'=>$disabled,
		];
		$model = $this->model;
		$name = $this->name;
		$competition = $this->competition;
		if ($competition != null) {
			$fee = 0;
			$originFee = $competition->associatedEvents[$event]['fee'];
			if ($competition instanceof Competition && isset($competition->associatedEvents[$event]) && $originFee > 0) {
				$fee = $competition->getEventFee($event);
				$text .= Html::fontAwesome('rmb', 'b') . $fee;
			}
			$options['data-fee'] = $fee;
			$options['data-origin-fee'] = $originFee;
		}
		echo CHtml::checkBox(CHtml::activeName($model, $name . '[]'), in_array("$event", $model->$name), $options);
		echo $text;
		echo CHtml::closeTag('label');
		echo CHtml::closeTag('div');
		echo '<br>';
	}
}
