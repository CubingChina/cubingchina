<?php

/**
 * This is the model class for table "pay_event".
 *
 * The followings are the available columns in table 'pay_event':
 * @property string $id
 * @property string $pay_id
 * @property string $registration_event_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class PayEvent extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'pay_event';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			['pay_id', 'required'],
			['status', 'numerical', 'integerOnly'=>true],
			['pay_id, create_time, update_time', 'length', 'max'=>11],
			['registration_event_id', 'length', 'max'=>6],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			['id, pay_id, registration_event_id, status, create_time, update_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'registrationEvent'=>[self::BELONGS_TO, 'RegistrationEvent', 'registration_event_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>Yii::t('PayEvent', 'ID'),
			'pay_id'=>Yii::t('PayEvent', 'Pay'),
			'registration_event_id'=>Yii::t('PayEvent', 'Registration Event'),
			'status'=>Yii::t('PayEvent', 'Status'),
			'create_time'=>Yii::t('PayEvent', 'Create Time'),
			'update_time'=>Yii::t('PayEvent', 'Update Time'),
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
		$criteria->compare('pay_id', $this->pay_id, true);
		$criteria->compare('registration_event_id', $this->registration_event_id, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PayEvent the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
