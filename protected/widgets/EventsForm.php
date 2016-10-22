<?php

class EventsForm extends Widget {
	public $model;
	public $competition;
	public $name = 'events';
	public $events = array();
	public $type = 'checkbox';
	public $htmlOptions = array();
	public $labelOptions = array();
	public $numberOptions = array();
	public $feeOptions = array();
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
				echo CHtml::openTag('div', array(
					'class'=>'checkbox checkbox-inline',
				));
				echo CHtml::openTag('label', array(
					'class'=>'event-icon event-icon-' . $event,
				));
				$text = $value;
				$fee = 0;
				$originFee = $competition->events[$event]['fee'];
				if ($competition instanceof Competition && isset($competition->events[$event]) && $originFee > 0) {
					$fee = $competition->getEventFee($event);
					$text .= Html::fontAwesome('rmb', 'b') . $fee;
				}
				echo CHtml::checkBox(CHtml::activeName($model, $name . '[]'), in_array("$event", $model->$name), array(
					'id'=>'Registration_events_' . $event,
					'class'=>'registration-events',
					'value'=>$event,
					'data-fee'=>$fee,
					'data-origin-fee'=>$originFee,
				));
				echo $text;
				echo CHtml::closeTag('label');
				echo CHtml::closeTag('div');
				echo '<br>';
			}
			echo CHtml::error($model, 'events', array('class'=>'text-danger'));
			echo CHtml::closeTag('div');
			if ($competition->isMultiLocation()) {
				echo CHtml::closeTag('div');
				$locations = array();
				foreach ($competition->sortedLocations as $location) {
					$locations[$location->location_id] = $competition->multi_countries ? $location->getCityName() : $location->getFullAddress(false);
				}
				echo CHtml::activeLabelEx($model, 'location_id');
				echo CHtml::activeDropDownList($model, 'location_id', $locations, array(
					'class'=>'form-control',
					'prompt'=>'',
				));
				echo CHtml::error($model, 'location_id', array('class'=>'text-danger'));
				echo CHtml::openTag('div', array(
					'class'=>'form-group',
				));
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
			foreach ($events as $key=>$value) {
				echo CHtml::openTag('div', array(
					'class'=>'col-lg-12',
				));
				echo CHtml::openTag('div', array(
					'class'=>'row',
				));
				//label
				$labelOptions['label'] = $value . ': ';
				echo CHtml::activeLabelEx($model, "{$name}[{$key}][round]", $labelOptions);
				//round
				echo CHtml::openTag('div', array(
					'class'=>'col-md-3 col-sm-8',
				));
				echo CHtml::openTag('div', array(
					'class'=>'input-group',
				));
				echo CHtml::activeNumberField($model, "{$name}[{$key}][round]", $numberOptions);
				echo CHtml::tag('span', array('class'=>'input-group-addon'), Yii::t('common', 'Rounds'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				//fee
				echo CHtml::openTag('div', array(
					'class'=>'col-md-7 row',
				));
				//normal fee
				echo CHtml::openTag('div', array(
					'class'=>'col-xs-4',
				));
				echo CHtml::openTag('div', array(
					'class'=>'input-group',
				));
				echo CHtml::activeNumberField($model, "{$name}[{$key}][fee]", $feeOptions);
				echo CHtml::tag('span', array('class'=>'input-group-addon'), Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				//second
				echo CHtml::openTag('div', array(
					'class'=>'col-xs-4',
				));
				echo CHtml::openTag('div', array(
					'class'=>'input-group',
				));
				echo CHtml::activeNumberField($model, "{$name}[{$key}][fee_second]", $feeOptions);
				echo CHtml::tag('span', array('class'=>'input-group-addon'), Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				//third
				echo CHtml::openTag('div', array(
					'class'=>'col-xs-4',
				));
				echo CHtml::openTag('div', array(
					'class'=>'input-group',
				));
				echo CHtml::activeNumberField($model, "{$name}[{$key}][fee_third]", $feeOptions);
				echo CHtml::tag('span', array('class'=>'input-group-addon'), Yii::t('common', 'CNY'));
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');

				echo CHtml::closeTag('div');
				echo CHtml::closeTag('div');
			}
			echo CHtml::closeTag('div');
		}
	}
}