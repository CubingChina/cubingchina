<?php
use Ramsey\Uuid\Uuid;

/**
 * This is the model class for table "user_ticket".
 *
 * The followings are the available columns in table 'user_ticket':
 * @property string $id
 * @property string $ticket_id
 * @property string $user_id
 * @property string $total_amount
 * @property string $paid_amount
 * @property string $paid_time
 * @property integer $discount
 * @property string $name
 * @property integer $passport_type
 * @property string $passport_name
 * @property string $passport_number
 * @property string $code
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 * @property string $cancel_time
 */
class UserTicket extends ActiveRecord {

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;

	public $repeatPassportNumber;

	public $coefficients = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
	public $codes = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];

	public function calculateFee() {
		$user = $this->user;
		$ticket = $this->ticket;
		if ($ticket === null) {
			return;
		}
		$this->total_amount = $ticket->fee * 100;
		switch ($ticket->type) {
			case Ticket::TYPE_COMPETITION:
				if ($this->hasDiscount()) {
					$this->discount = Ticket::CHILDREN_DISCOUNT;
				}
				break;
		}
	}

	public function createPayment() {
		if ($this->payment === null) {
			$payment = new Pay();
			$payment->user_id = $this->user_id;
			$payment->type = Pay::TYPE_TICKET;
			$payment->type_id = $this->ticket_id;
			$payment->sub_type_id = $this->id;
			$payment->order_name = $this->ticket->name_zh;
			$payment->amount = $this->total_amount;
			if ($this->discount > 0) {
				$payment->amount = $this->total_amount * $this->discount / 100;
			}
			$payment->save();
			$this->payment = $payment;
		}
		return $this->payment;
	}

	public function hasDiscount($competition = null) {
		$user = $this->user;
		if ($competition === null) {
			$competition = $this->ticket->competition;
		}
		$registration = Registration::getUserRegistration($competition->id, $user->id);
		return $registration !== null && ($registration->isAccepted() || $registration->isDisqualified()) && $competition->date - $user->birthday <= 14 * 365.25 * 86400 && self::model()->countByAttributes([
			'ticket_id'=>$competition->getTicketIds(),
			'user_id'=>$this->user_id,
		], [
			'condition'=>'discount > 0',
		]) == 0;
	}

	public function getFee() {
		return Html::fontAwesome('rmb') . ($this->total_amount * ($this->discount ?: 100) / 10000);
	}

	public function getQRCodeUrl() {
		if ($this->code == '') {
			$this->code = substr(sprintf('ticket-%s-%s', Uuid::uuid1(), Uuid::uuid4()), 0, 64);
			$this->save();
		}
		return CHtml::normalizeUrl([
			'/qrCode/ticket',
			'code'=>$this->code,
		]);
	}

	public function getDataForSignin() {
		return [
			'type'=>'ticket',
			'title'=>$this->ticket->getAttributeValue('name'),
			'id'=>$this->id,
			'passport'=>$this->passport_number,
			'user'=>[
				'name'=>$this->name,
			],
			'fee'=>$this->total_amount * ($this->discount ?: 100) / 10000,
			'paid'=>$this->isPaid(),
			'signed_in'=>!!$this->signed_in,
			'signed_date'=>date('Y-m-d H:i:s', $this->signed_date),
		];
	}

	public function getPassportTypeText() {
		$types = User::getPassportTypes();
		$text = $types[$this->passport_type] ?? $this->passport_type;
		if ($this->passport_type == User::PASSPORT_TYPE_OTHER) {
			$text .= "($this->passport_name)";
		}
		return $text;
	}

	public function accept() {
		$this->status = self::STATUS_PAID;
		$this->paid_amount = $this->payment->paid_amount;
		$this->paid_time = $this->payment->paid_time;
		$this->code = substr(sprintf('ticket-%s-%s', Uuid::uuid1(), Uuid::uuid4()), 0, 64);
		$this->save(false);
	}

	public function checkPassportType() {
		if ($this->passport_type == User::PASSPORT_TYPE_OTHER && empty($this->passport_name)) {
			$this->addError('passport_name', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('passport_name'),
			)));
		}
	}

	public function checkPassportNumber() {
		switch ($this->passport_type) {
			case User::PASSPORT_TYPE_ID:
				if (!preg_match('|^\d{17}[\dX]$|i', $this->passport_number)) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				$sum = 0;
				for ($i = 0; $i < 17; $i++) {
					$sum += $this->passport_number[$i] * $this->coefficients[$i];
				}
				$mod = $sum % 11;
				if (strtoupper($this->passport_number[17]) != $this->codes[$mod]) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
			case User::PASSPORT_TYPE_PASSPORT:
				if (!preg_match('|^\w+$|i', $this->passport_number, $matches)) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
			case User::NO:
				$this->passport_number = '';
				break;
		}
	}

	public function checkTicket() {
		if ($this->ticket && !$this->ticket->isAvailable()) {
			$this->addError('ticket_id', Yii::t('Ticket', 'The ticket you chose is not available.'));
			return false;
		}
	}

	public function isPayable() {
		return $this->isUnpaid() && !$this->ticket->isSoldOut();
	}

	public function isPaid() {
		return $this->status == self::STATUS_PAID;
	}

	public function isUnpaid() {
		return $this->status == self::STATUS_UNPAID;
	}

	public function isEditable() {
		return !$this->signed_in && ($this->isPaid() || !$this->ticket->isSoldOut());
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user_ticket';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return [
			['ticket_id', 'required', 'message'=>Yii::t('Competition', 'Please choose a ticket!')],
			['ticket_id', 'checkTicket'],
			['name, passport_type, passport_number', 'required'],
			['discount, passport_type, status', 'numerical', 'integerOnly'=>true],
			['id', 'length', 'max'=>32],
			['ticket_id, user_id, total_amount, paid_amount, paid_time, create_time, update_time, cancel_time', 'length', 'max'=>11],
			['name, passport_name', 'length', 'max'=>100],
			['passport_number', 'length', 'max'=>50],
			['repeatPassportNumber', 'required', 'on'=>['insert', 'edit']],
			['repeatPassportNumber', 'compare', 'compareAttribute'=>'passport_number', 'on'=>['insert', 'edit']],
			['passport_type', 'checkPassportType'],
			['passport_number', 'checkPassportNumber'],
			['code', 'length', 'max'=>64],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			['id, ticket_id, user_id, total_amount, paid_amount, paid_time, discount, name, passport_type, passport_name, passport_number, code, status, create_time, update_time, cancel_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'ticket'=>[self::BELONGS_TO, 'Ticket', 'ticket_id'],
			'user'=>[self::BELONGS_TO, 'User', 'user_id'],
			'payment'=>[self::HAS_ONE, 'Pay', 'sub_type_id', 'on'=>'payment.type=' . Pay::TYPE_TICKET],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>Yii::t('UserTicket', 'ID'),
			'ticket_id'=>Yii::t('UserTicket', 'Ticket'),
			'user_id'=>Yii::t('UserTicket', 'User'),
			'total_amount'=>Yii::t('UserTicket', 'Total Amount'),
			'paid_amount'=>Yii::t('UserTicket', 'Paid Amount'),
			'paid_time'=>Yii::t('UserTicket', 'Paid Time'),
			'discount'=>Yii::t('UserTicket', 'Discount'),
			'name'=>Yii::t('UserTicket', 'Name'),
			'fee'=>Yii::t('common', 'Fee'),
			'passport_type' => Yii::t('Registration', 'Type of Identity'),
			'passport_name' => Yii::t('Registration', 'Name of Identity'),
			'passport_number' => Yii::t('Registration', 'Identity Number'),
			'repeatPassportNumber' => Yii::t('Registration', 'Repeat Identity Number'),
			'code'=>Yii::t('UserTicket', 'Code'),
			'status'=>Yii::t('UserTicket', 'Status'),
			'create_time'=>Yii::t('UserTicket', 'Create Time'),
			'update_time'=>Yii::t('UserTicket', 'Update Time'),
			'cancel_time'=>Yii::t('UserTicket', 'Cancel Time'),
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
		$criteria->compare('ticket_id', $this->ticket_id, true);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('total_amount', $this->total_amount, true);
		$criteria->compare('paid_amount', $this->paid_amount, true);
		$criteria->compare('paid_time', $this->paid_time, true);
		$criteria->compare('discount', $this->discount);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('passport_type', $this->passport_type);
		$criteria->compare('passport_name', $this->passport_name, true);
		$criteria->compare('passport_number', $this->passport_number, true);
		$criteria->compare('code', $this->code, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);
		$criteria->compare('cancel_time', $this->cancel_time, true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserTicket the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
