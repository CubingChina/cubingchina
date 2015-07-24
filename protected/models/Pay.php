<?php

/**
 * This is the model class for table "pay".
 *
 * The followings are the available columns in table 'pay':
 * @property string $id
 * @property string $user_id
 * @property string $channel
 * @property integer $type
 * @property string $type_id
 * @property string $sub_type_id
 * @property string $order_id
 * @property string $order_name
 * @property string $amount
 * @property integer $device_type
 * @property string $pay_channel
 * @property string $pay_account
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Pay extends ActiveRecord {
	const TYPE_REGISTRATION = 0;

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	const STATUS_FAILED = 2;

	const DEVICE_TYPE_PC = '02';
	const DEVICE_TYPE_WAP = '06';

	//现在支付参数
	const NOWPAY_FUNCODE_PAY = 'WP001';
	const NOWPAY_FUNCODE_NOTIFY = 'N001';
	const NOWPAY_FUNCODE_FRONT_NOTIFY = 'N002';
	const NOWPAY_TRADE_SUCCESS = 'A001';
	const NOWPAY_ORDER_TYPE = '01';
	const NOWPAY_ORDER_TIME_OUT = 3600;
	const NOWPAY_CURRENCY_TYPE = '156';

	const CHARSET = 'UTF-8';
	const SIGN_TYPE = 'MD5';

	public static function buildNowPaySignature(&$params, $securityKey, $excludeAttributes = array()) {
		$temp = array_filter($params);
		foreach ($excludeAttributes as $attribute) {
			unset($temp[$attribute]);
		}
		ksort($temp);
		$str = '';
		foreach ($temp as $key=>$value) {
			$str .= "$key=$value&";
		}
		$str .= md5($securityKey);
		$params['mhtSignature'] = md5($str);
		$params['mhtSignType'] = self::SIGN_TYPE;
		return $params;
	}

	public static function buildAlipaySignature(&$params, $securityKey, $excludeAttributes = array()) {
		$temp = array_filter($params);
		foreach ($excludeAttributes as $attribute) {
			unset($temp[$attribute]);
		}
		ksort($temp);
		$str = array();
		foreach ($temp as $key=>$value) {
			$str[] = "$key=$value";
		}
		$str = implode('&');
		$str .= md5($securityKey);
		$params['sign'] = md5($str);
		$params['sign_type'] = self::SIGN_TYPE;
		return $params;
	}

	public static function getPayByOrderId($orderId) {
		return self::model()->findByAttributes(array(
			'order_id'=>$orderId,
		));
	}

	public static function getAllStatus() {
		return array(
			self::STATUS_UNPAID=>Yii::t('common', 'Unpaid'), 
			self::STATUS_PAID=>Yii::t('common', 'Paid'), 
		);
	}

	public static function getTypes() {
		return array(
			self::TYPE_REGISTRATION=>Yii::t('common', 'Registration'), 
		);
	}

	public function validateNowPayNotify($params) {
		$app = Yii::app();
		$appId = isset($params['appId']) ? $params['appId'] : '';
		$signature = isset($params['signature']) ? $params['signature'] : '';
		$funcode = isset($params['funcode']) ? $params['funcode'] : '';
		$deviceType = isset($params['deviceType']) ? $params['deviceType'] : '';
		$tradeStatus = isset($params['tradeStatus']) ? $params['tradeStatus'] : '';
		$payChannelType = isset($params['payChannelType']) ? $params['payChannelType'] : '';
		$nowPayAccNo = isset($params['nowPayAccNo']) ? $params['nowPayAccNo'] : '';
		foreach ($app->params->nowPay['types'] as $type) {
			if ($type['appId'] === $appId) {
				$nowPay = $type;
				break;
			}
		}
		if (!isset($nowPay)) {
			return false;
		}
		self::buildNowPaySignature($params, $nowPay['securityKey'], array(
			'signature',
			'signType',
		));
		$result = $signature === $params['mhtSignature'] && $tradeStatus === self::NOWPAY_TRADE_SUCCESS;
		if ($result) {
			switch ($funcode) {
				case self::NOWPAY_FUNCODE_NOTIFY:
					$this->device_type = $deviceType;
					$this->pay_channel = $payChannelType;
					$this->pay_account = $nowPayAccNo;
					break;
			}
			$this->successPaid();
		}
		return $result;
	}

	public function successPaid() {
		$this->status = self::STATUS_PAID;
		$this->update_time = time();
		$this->save(false);
		switch ($this->type) {
			case self::TYPE_REGISTRATION:
				$registration = $this->registration;
				if ($registration !== null) {
					$registration->status = Registration::STATUS_ACCEPTED;
					$registration->paid = Registration::PAID;
					$registration->save();
				}
				break;
		}
	}

	public function generateNowPayUrl($isMobile) {
		$app = Yii::app();
		if ($isMobile) {
			$nowPay = $app->params->nowPay['types']['mobile'];
		} else {
			$nowPay = $app->params->nowPay['types']['pc'];
		}
		$baseUrl = $app->request->getBaseUrl(true);
		$params = array(
			'funcode'=>self::NOWPAY_FUNCODE_PAY,
			'appId'=>$nowPay['appId'],
			'mhtOrderNo'=>$this->order_id,
			'mhtOrderName'=>$this->order_name,
			'mhtOrderType'=>self::NOWPAY_ORDER_TYPE,
			'mhtCurrencyType'=>self::NOWPAY_CURRENCY_TYPE,
			'mhtOrderAmt'=>$this->amount,
			'mhtOrderDetail'=>$this->order_name,
			'mhtOrderTimeOut'=>self::NOWPAY_ORDER_TIME_OUT,
			'mhtOrderStartTime'=>date('YmdHis'),
			'notifyUrl'=>$baseUrl . $app->createUrl('/pay/notify'),
			'frontNotifyUrl'=>$baseUrl . $app->createUrl('/pay/frontNotify'),
			'mhtCharset'=>self::CHARSET,
			'deviceType'=>$nowPay['deviceType'],
			// 'mhtReserved'=>'',
			'consumerId'=>$this->user_id,
		);
		self::buildNowPaySignature($params, $nowPay['securityKey'], array(
			'funcode',
			'deviceType',
		));
		return $app->params->nowPay['baseUrl'] . '?' . http_build_query($params);
	}

	public function isPaid() {
		return $this->status == self::STATUS_PAID;
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getTypeText() {
		$types = self::getTypes();
		return isset($types[$this->type]) ? $types[$this->type] : $this->type;
	}

	public function getColumns() {
		$columns = array(
			'id',
			array(
				'name'=>'user_id',
				'value'=>'$data->user->name_zh',
			),
			array(
				'name'=>'type',
				'value'=>'$data->getTypeText()',
				'filter'=>Pay::getTypes(),
			),
			array(
				'name'=>'amount',
				'value'=>'number_format($data->amount / 100, 2)',
			),
			array(
				'name'=>'create_time',
				'type'=>'raw',
				'value'=>'date("Y-m-d H:i:s", $data->create_time)',
				'filter'=>false,
			),
			array(
				'name'=>'update_time',
				'type'=>'raw',
				'value'=>'date("Y-m-d H:i:s", $data->update_time)',
				'filter'=>false,
			),
			array(
				'name'=>'status',
				'type'=>'raw',
				'value'=>'$data->getStatusText()',
				'filter'=>Pay::getAllStatus(),
			),
		);
		if ($this->type !== '') {
			switch ($this->type) {
				case self::TYPE_REGISTRATION:
					array_splice($columns, 4, 0, array(
						array(
							'name'=>'type_id',
							'header'=>Yii::t('common', 'Competition'),
							'value'=>'$data->competition->name_zh',
							'filter'=>Competition::getRegistrationCompetitions(),
						),
					));
					break;
			}
		}
		return $columns;
	}

	protected function beforeValidate() {
		if ($this->isNewRecord) {
			$this->create_time = $this->update_time = time();
			if (!$this->order_id) {
				$this->order_id = sprintf('%s-%d-%06d-%05d', date('YmdHis', $this->create_time), $this->type, $this->sub_type_id, mt_rand(10000, 99999));
			}
		}
		return parent::beforeValidate();
	}

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
			array('user_id, type_id, sub_type_id, amount', 'length', 'max'=>10),
			array('channel', 'length', 'max'=>6),
			array('order_id', 'length', 'max'=>32),
			array('order_name', 'length', 'max'=>50),
			array('pay_channel', 'length', 'max'=>4),
			array('pay_account', 'length', 'max'=>64),
			array('create_time, update_time', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, channel, type, type_id, sub_type_id, order_id, order_name, amount, device_type, pay_channel, pay_account, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'competition'=>array(self::BELONGS_TO, 'Competition', 'type_id'),
			'registration'=>array(self::BELONGS_TO, 'Registration', 'sub_type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Pay', 'ID'),
			'user_id' => Yii::t('Pay', 'User'),
			'channel' => Yii::t('Pay', 'Channel'),
			'type' => Yii::t('Pay', 'Type'),
			'type_id' => Yii::t('Pay', 'Type'),
			'sub_type_id' => Yii::t('Pay', 'Sub Type'),
			'order_id' => Yii::t('Pay', 'Order'),
			'order_name' => Yii::t('Pay', 'Order Name'),
			'amount' => Yii::t('Pay', 'Amount'),
			'device_type' => Yii::t('Pay', 'Device Type'),
			'pay_channel' => Yii::t('Pay', 'Pay Channel'),
			'pay_account' => Yii::t('Pay', 'Pay Account'),
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('channel', $this->channel, true);
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id, true);
		$criteria->compare('sub_type_id', $this->sub_type_id, true);
		$criteria->compare('order_id', $this->order_id, true);
		$criteria->compare('order_name', $this->order_name, true);
		$criteria->compare('amount', $this->amount, true);
		$criteria->compare('device_type', $this->device_type);
		$criteria->compare('pay_channel', $this->pay_channel, true);
		$criteria->compare('pay_account', $this->pay_account, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'id DESC',
			),
			'pagination'=>array(
				'pageSize'=>100,
			),
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
