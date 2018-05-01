<?php

class GroupSchedule extends Schedule {
	public function relations() {
		$relations = parent::relations();
		$relations['users'] = [self::HAS_MANY, 'UserSchedule', 'group_id'];
		return $relations;
	}
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
