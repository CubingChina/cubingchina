<?php

/**
 * This is the model class for table 'pay'.
 *
 * The followings are the available columns in table 'pay':
 * @property string $id
 * @property string $user_id
 * @property string $channel
 * @property integer $type
 * @property string $type_id
 * @property string $sub_type_id
 * @property string $order_no
 * @property string $order_name
 * @property string $amount
 * @property integer $device_type
 * @property string $pay_channel
 * @property string $pay_account
 * @property string $trade_no
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Pay extends ActiveRecord {
	const CHANNEL_NOWPAY = 'nowPay';
	const CHANNEL_ALIPAY = 'alipay';

	const TYPE_REGISTRATION = 0;

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	const STATUS_FAILED = 2;
	const STATUS_WAIT_SEND = 3;
	const STATUS_WAIT_CONFIRM = 4;

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

	//支付宝
	const ALIPAY_TRADE_STATUS_WAIT_SEND = 'WAIT_SELLER_SEND_GOODS';
	const ALIPAY_TRADE_STATUS_WAIT_CONFIRM = 'WAIT_BUYER_CONFIRM_GOODS';
	const ALIPAY_TRADE_STATUS_FINISHED = 'TRADE_FINISHED';
	const ALIPAY_TRADE_STATUS_CLOSED = 'TRADE_CLOSED';
	const ALIPAY_SUCCESS = 'T';

	const CHARSET = 'UTF-8';
	const SIGN_TYPE = 'MD5';

	public static function notifyReturn($channel, $success) {
		switch ($channel) {
			case self::CHANNEL_NOWPAY:
				return $success ? 'success=Y' : 'success=N';
			default:
				return $success ? 'success' : 'fail';
		}
	}

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
		$str = implode('&', $str);
		$str .= $securityKey;
		$params['sign'] = md5($str);
		$params['sign_type'] = self::SIGN_TYPE;
		return $params;
	}

	public static function getPayByOrderId($orderId) {
		return self::model()->findByAttributes(array(
			'order_no'=>$orderId,
		));
	}

	public static function getAllStatus() {
		return array(
			self::STATUS_UNPAID=>Yii::t('common', 'Unpaid'), 
			self::STATUS_PAID=>Yii::t('common', 'Paid'), 
			self::STATUS_WAIT_SEND=>'待发货', 
			self::STATUS_WAIT_CONFIRM=>'待收货', 
		);
	}

	public static function getChannels() {
		return array(
			self::CHANNEL_ALIPAY=>'支付宝', 
			self::CHANNEL_NOWPAY=>'现在支付', 
		);
	}

	public static function getTypes() {
		return array(
			self::TYPE_REGISTRATION=>Yii::t('common', 'Registration'), 
		);
	}

	public function validateNotify($channel, $params) {
		switch ($channel) {
			case self::CHANNEL_NOWPAY:
				return $this->validateNowPayNotify($params);
			default:
				return $this->validateAlipayNotify($params);
		}
	}

	public function validateAlipayNotify($params) {
		$app = Yii::app();
		$alipay = $app->params->alipay;
		$sign = isset($params['sign']) ? $params['sign'] : '';
		$tradeStatus = isset($params['trade_status']) ? $params['trade_status'] : '';
		$buyerEmail = isset($params['buyer_email']) ? $params['buyer_email'] : '';
		$tradeNo = isset($params['trade_no']) ? $params['trade_no'] : '';
		self::buildAlipaySignature($params, $alipay['key'], array(
			'sign',
			'sign_type',
		));
		$result = $sign === $params['sign'];
		if ($result) {
			$this->trade_no = $tradeNo;
			$this->pay_account = $buyerEmail;
			$this->channel = self::CHANNEL_ALIPAY;
			$status = self::STATUS_WAIT_SEND;
			switch ($tradeStatus) {
				case self::ALIPAY_TRADE_STATUS_WAIT_SEND:
					$status = self::STATUS_WAIT_SEND;
					if ($this->send()) {
						$status = self::STATUS_WAIT_CONFIRM;
					}
					break;
				case self::ALIPAY_TRADE_STATUS_WAIT_CONFIRM:
					$status = self::STATUS_WAIT_CONFIRM;
					break;
				case self::ALIPAY_TRADE_STATUS_FINISHED:
					$status = self::STATUS_PAID;
					break;
			}
			$this->updateStatus($status);
		}
		return $result;
	}

	public function send() {
		$app = Yii::app();
		$alipay = $app->params->alipay;
		$params = array(
			'service'=>'send_goods_confirm_by_platform',
			'partner'=>$alipay['partner'],
			'trade_no'=>$this->trade_no,
			'logistics_name'=>$app->name,
			// 'invoice_no'=>'',
			'transport_type'=>'EXPRESS',
			'_input_charset'=>strtolower(self::CHARSET),
		);
		self::buildAlipaySignature($params, $alipay['key']);
		$content = file_get_contents($alipay['gateway'] . '?' . http_build_query($params));
		return strpos($content, '<is_success>T</is_success>') !== false;
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
			$this->channel = self::CHANNEL_NOWPAY;
			$this->updateStatus();
		}
		return $result;
	}

	public function updateStatus($status = self::STATUS_PAID) {
		if (!$this->isPaid()) {
			$this->status = $status;
		}
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

	public function generateParams($isMobile, $channel) {
		switch ($channel) {
			case self::CHANNEL_NOWPAY:
				return $this->generateNowPayParams($isMobile);
			default:
				return $this->generateAlipayParams();
		}
	}

	public function generateAlipayParams() {
		$app = Yii::app();
		$alipay = $app->params->alipay;
		$baseUrl = $app->request->getBaseUrl(true);
		$language = $app->language;
		$app->language = 'zh_cn';
		$params = array(
			'service'=>'create_partner_trade_by_buyer',
			'partner'=>trim($alipay['partner']),
			'seller_email'=>trim($alipay['seller_email']),
			'payment_type'=>1,
			'notify_url'=>$baseUrl . $app->createUrl('/pay/notify', array('channel'=>self::CHANNEL_ALIPAY)),
			'return_url'=>$baseUrl . $app->createUrl('/pay/frontNotify', array('channel'=>self::CHANNEL_ALIPAY)),
			'out_trade_no'=>$this->order_no,
			'subject'=>$this->order_name,
			'price'=>number_format($this->amount / 100, 2, '.', ''),
			'quantity'=>1,
			'logistics_fee'=>'0.00',
			'logistics_type'=>'EXPRESS',
			'logistics_payment'=>'SELLER_PAY',
			'body'=>$this->order_name,
			// 'show_url'=>$show_url,
			'receive_name'=>$this->user->getCompetitionName(),
			'receive_address'=>$this->user->getRegionName($this->user->country) . $this->user->getRegionName($this->user->province) . $this->user->getRegionName($this->user->city),
			// 'receive_zip'=>$receive_zip,
			// 'receive_phone'=>$receive_phone,
			'receive_mobile'=>$this->user->mobile,
			'_input_charset'=>strtolower(self::CHARSET),
		);
		self::buildAlipaySignature($params, $alipay['key']);
		return array(
			'action'=>$alipay['gateway'] . '?_input_charset=utf-8',
			'method'=>'post',
			'params'=>$params,
		);
	}

	public function generateNowPayParams($isMobile) {
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
			'mhtOrderNo'=>$this->order_no,
			'mhtOrderName'=>$this->order_name,
			'mhtOrderType'=>self::NOWPAY_ORDER_TYPE,
			'mhtCurrencyType'=>self::NOWPAY_CURRENCY_TYPE,
			'mhtOrderAmt'=>$this->amount,
			'mhtOrderDetail'=>$this->order_name,
			'mhtOrderTimeOut'=>self::NOWPAY_ORDER_TIME_OUT,
			'mhtOrderStartTime'=>date('YmdHis'),
			'notifyUrl'=>$baseUrl . $app->createUrl('/pay/notify', array('channel'=>self::CHANNEL_NOWPAY)),
			'frontNotifyUrl'=>$baseUrl . $app->createUrl('/pay/frontNotify', array('channel'=>self::CHANNEL_NOWPAY)),
			'mhtCharset'=>self::CHARSET,
			'deviceType'=>$nowPay['deviceType'],
			// 'mhtReserved'=>'',
			'consumerId'=>$this->user_id,
		);
		self::buildNowPaySignature($params, $nowPay['securityKey'], array(
			'funcode',
			'deviceType',
		));
		return array(
			'action'=>$app->params->nowPay['baseUrl'],
			'method'=>'get',
			'params'=>$params,
		);
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
				'name'=>'channel',
				'value'=>'$data->channel',
				'filter'=>Pay::getChannels(),
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
			if (!$this->order_no) {
				$this->order_no = sprintf('%s-%d-%06d-%05d', date('YmdHis', $this->create_time), $this->type, $this->sub_type_id, mt_rand(10000, 99999));
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
			array('user_id, order_no, order_name', 'required'),
			array('type, device_type, status', 'numerical', 'integerOnly'=>true),
			array('user_id, type_id, sub_type_id, amount', 'length', 'max'=>10),
			array('channel', 'length', 'max'=>6),
			array('order_no', 'length', 'max'=>32),
			array('order_name', 'length', 'max'=>50),
			array('pay_channel', 'length', 'max'=>4),
			array('pay_account, trade_no', 'length', 'max'=>64),
			array('create_time, update_time', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, channel, type, type_id, sub_type_id, order_no, order_name, amount, device_type, pay_channel, pay_account, trade_no, status, create_time, update_time', 'safe', 'on'=>'search'),
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
			'id'=>Yii::t('Pay', 'ID'),
			'user_id'=>Yii::t('Pay', 'User'),
			'channel'=>Yii::t('Pay', 'Channel'),
			'type'=>Yii::t('Pay', 'Type'),
			'type_id'=>Yii::t('Pay', 'Type'),
			'sub_type_id'=>Yii::t('Pay', 'Sub Type'),
			'order_no'=>Yii::t('Pay', 'Order No'),
			'order_name'=>Yii::t('Pay', 'Order Name'),
			'amount'=>Yii::t('Pay', 'Amount'),
			'device_type'=>Yii::t('Pay', 'Device Type'),
			'pay_channel'=>Yii::t('Pay', 'Pay Channel'),
			'pay_account'=>Yii::t('Pay', 'Pay Account'),
			'trade_no'=>Yii::t('Pay', 'Trade No'),
			'status'=>Yii::t('Pay', 'Status'),
			'create_time'=>Yii::t('Pay', 'Create Time'),
			'update_time'=>Yii::t('Pay', 'Update Time'),
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
		$criteria->compare('order_no', $this->order_no, true);
		$criteria->compare('order_name', $this->order_name, true);
		$criteria->compare('amount', $this->amount, true);
		$criteria->compare('device_type', $this->device_type);
		$criteria->compare('pay_channel', $this->pay_channel, true);
		$criteria->compare('pay_account', $this->pay_account, true);
		$criteria->compare('trade_no', $this->trade_no, true);
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
