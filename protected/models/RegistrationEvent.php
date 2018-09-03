<?php

/**
 * This is the model class for table "registration_event".
 *
 * The followings are the available columns in table 'registration_event':
 * @property string $id
 * @property string $registration_id
 * @property string $event
 * @property integer $fee
 * @property integer $paid
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 * @property string $accept_time
 */
class RegistrationEvent extends ActiveRecord {

	const STATUS_PENDING = 0;
	const STATUS_ACCEPTED = 1;
	const STATUS_CANCELLED = 2;
	const STATUS_CANCELLED_TIME_END = 3;
	const STATUS_CANCELLED_QUALIFYING_TIME = 4;
	const STATUS_WAITING = 5;

	public function isPending() {
		return $this->status == self::STATUS_PENDING;
	}

	public function isAccepted() {
		return $this->status == self::STATUS_ACCEPTED;
	}

	public function isCancelled() {
		return $this->status == self::STATUS_CANCELLED
			|| $this->status == self::STATUS_CANCELLED_TIME_END
			|| $this->isDisqualified();
	}

	public function isDisqualified() {
		return $this->status == self::STATUS_CANCELLED_QUALIFYING_TIME;
	}

	public function isWaiting() {
		return $this->status == self::STATUS_WAITING;
	}

	public function isPaid() {
		return $this->paid == Registration::PAID;
	}

	public function accept($forceAccept = false) {
		if ($this->isCancelled()) {
			return false;
		}
		$this->status = self::STATUS_ACCEPTED;
		if ($this->accept_time == 0) {
			$this->accept_time = time();
		}
		return $this->save();
	}

	public function cancel($status = self::STATUS_CANCELLED) {
		$this->status = $status;
		return $this->save();
	}

	public function disqualify() {
		$this->status = self::STATUS_CANCELLED_QUALIFYING_TIME;
		return $this->save();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'registration_event';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			['registration_id', 'required'],
			['fee, paid, status', 'numerical', 'integerOnly'=>true],
			['registration_id, create_time, update_time, accept_time', 'length', 'max'=>11],
			['event', 'length', 'max'=>6],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			['id, registration_id, event, fee, paid, status, create_time, update_time, accept_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'registration'=>[self::BELONGS_TO, 'Registration', 'registration_id'],
			'payEvent'=>[self::HAS_ONE, 'PayEvent', 'registration_event_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>Yii::t('RegistrationEvent', 'ID'),
			'registration_id'=>Yii::t('RegistrationEvent', 'Registration'),
			'event'=>Yii::t('RegistrationEvent', 'Event'),
			'fee'=>Yii::t('RegistrationEvent', 'Fee'),
			'paid'=>Yii::t('RegistrationEvent', 'Paid'),
			'status'=>Yii::t('RegistrationEvent', 'Status'),
			'create_time'=>Yii::t('RegistrationEvent', 'Create Time'),
			'update_time'=>Yii::t('RegistrationEvent', 'Update Time'),
			'accept_time'=>Yii::t('RegistrationEvent', 'Accept Time'),
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

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('registration_id', $this->registration_id, true);
		$criteria->compare('event', $this->event, true);
		$criteria->compare('fee', $this->fee);
		$criteria->compare('paid', $this->paid);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);
		$criteria->compare('accept_time', $this->accept_time, true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RegistrationEvent the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
