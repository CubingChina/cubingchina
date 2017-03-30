<?php

/**
 * This is the model class for table "preferred_event".
 *
 * The followings are the available columns in table 'preferred_event':
 * @property string $id
 * @property string $user_id
 * @property string $event
 */
class PreferredEvent extends ActiveRecord {

	public static function getUserEvents($user) {
		return CHtml::listData(self::model()->findAllByAttributes([
			'user_id'=>$user->id,
		]), 'event', 'event');
	}

	public static function updateUserEvents($user) {
		self::model()->deleteAllByAttributes([
			'user_id'=>$user->id,
		]);
		foreach ($user->preferredEvents as $event) {
			if (empty($event)) {
				continue;
			}
			$preferredEvent = new PreferredEvent();
			$preferredEvent->user_id = $user->id;
			$preferredEvent->event = $event;
			$preferredEvent->save();
		}
		return true;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'preferred_event';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			array('user_id', 'required'),
			array('user_id', 'length', 'max'=>11),
			array('event', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, event', 'safe', 'on'=>'search'),
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'user_id' => 'User',
			'event' => 'Event',
		];
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id,true);
		$criteria->compare('user_id', $this->user_id,true);
		$criteria->compare('event', $this->event,true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PreferredEvent the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
