<?php

/**
 * This is the model class for table "pay".
 *
 * The followings are the available columns in table 'pay':
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $type_id
 * @property string $order_id
 * @property string $order_name
 * @property string $amount
 * @property integer $device_type
 * @property string $pay_channel
 * @property string $now_pay_account
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Pay extends ActiveRecord {
	const TYPE_REGISTRATION = 0;

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	const STATUS_FAILED = 2;

	const DEVICE_TYPE_PC = 0;
	const DEVICE_TYPE_WAP = 1;

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'pay';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, order_id, order_name', 'required'),
			array('type, device_type, status', 'numerical', 'integerOnly'=>true),
			array('user_id, type_id, amount', 'length', 'max'=>10),
			array('order_id', 'length', 'max'=>20),
			array('order_name', 'length', 'max'=>50),
			array('pay_channel', 'length', 'max'=>4),
			array('now_pay_account', 'length', 'max'=>64),
			array('create_time, update_time', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, type, type_id, order_id, order_name, amount, device_type, pay_channel, now_pay_account, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Pay', 'ID'),
			'user_id' => Yii::t('Pay', 'User'),
			'type' => Yii::t('Pay', 'Type'),
			'type_id' => Yii::t('Pay', 'Type'),
			'order_id' => Yii::t('Pay', 'Order'),
			'order_name' => Yii::t('Pay', 'Order Name'),
			'amount' => Yii::t('Pay', 'Amount'),
			'device_type' => Yii::t('Pay', 'Device Type'),
			'pay_channel' => Yii::t('Pay', 'Pay Channel'),
			'now_pay_account' => Yii::t('Pay', 'Now Pay Account'),
			'status' => Yii::t('Pay', 'Status'),
			'create_time' => Yii::t('Pay', 'Create Time'),
			'update_time' => Yii::t('Pay', 'Update Time'),
		);
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('type_id',$this->type_id,true);
		$criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('order_name',$this->order_name,true);
		$criteria->compare('amount',$this->amount,true);
		$criteria->compare('device_type',$this->device_type);
		$criteria->compare('pay_channel',$this->pay_channel,true);
		$criteria->compare('now_pay_account',$this->now_pay_account,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('create_time',$this->create_time,true);
		$criteria->compare('update_time',$this->update_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pay the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
