<?php

class HeatSchedule extends Schedule {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'heat_schedule';
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}