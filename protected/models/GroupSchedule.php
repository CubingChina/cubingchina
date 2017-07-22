<?php

class GroupSchedule extends Schedule {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'group_schedule';
	}

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
