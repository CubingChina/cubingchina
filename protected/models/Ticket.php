<?php

/**
 * This is the model class for table "ticket".
 *
 * The followings are the available columns in table 'ticket':
 * @property string $id
 * @property integer $type
 * @property string $type_id
 * @property string $name
 * @property string $name_zh
 * @property string $description
 * @property string $description_zh
 * @property string $fee
 * @property string $purchase_deadline
 * @property string $number
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Ticket extends ActiveRecord {

	const TYPE_COMPETITION = 0;
	const CHILDREN_DISCOUNT = 60;

	private $_stock;

	public function getStock() {
		if ($this->_stock !== null) {
			return $this->_stock;
		}
		$number = $this->number;
		$number -= $this->soldTicketsNum;
		if ($this->multi_days) {
			// maybe need to minus single day tickets
		} else {
			$soldMultiDaysTicketsNum = $this->dbConnection->createCommand()
				->select('count(user_ticket.id)')
				->from('user_ticket')
				->leftJoin('ticket', 'ticket.id=user_ticket.ticket_id')
				->where('ticket.type=:type AND ticket.type_id=:type_id AND ticket.multi_days=1 AND user_ticket.status=1', [
					':type'=>$this->type,
					':type_id'=>$this->type_id,
				])
				->queryScalar();
			$number -= $soldMultiDaysTicketsNum;
		}
		return $this->_stock = max($number, 0);
	}

	public function isSoldOut() {
		return $this->stock <= 0;
	}

	public function isAvailable() {
		if ($this->isSoldOut()) {
			return false;
		}
		if (!Yii::app()->controller->user) {
			return true;
		}
		return array_filter(Yii::app()->controller->user->getUnpaidTickets($this->competition), function($userTicket) {
			return $userTicket->ticket_id == $this->id;
		}) === [];
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ticket';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			['name, name_zh, description, description_zh', 'required'],
			['type, status', 'numerical', 'integerOnly'=>true],
			['type_id, fee, purchase_deadline, number, create_time, update_time', 'length', 'max'=>11],
			['name, name_zh', 'length', 'max'=>32],
			['description, description_zh', 'length', 'max'=>2048],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			['id, type, type_id, name, name_zh, description, description_zh, fee, purchase_deadline, number, status, create_time, update_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'userTickets'=>[self::HAS_MANY, 'UserTicket', 'ticket_id'],
			'competition'=>[self::BELONGS_TO, 'Competition', 'type_id'],
			'soldTicketsNum'=>[self::STAT, 'UserTicket', 'ticket_id', 'condition'=>'status=1'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>Yii::t('Ticket', 'ID'),
			'type'=>Yii::t('Ticket', 'Type'),
			'type_id'=>Yii::t('Ticket', 'Type'),
			'name'=>Yii::t('Ticket', 'Name'),
			'name_zh'=>Yii::t('Ticket', 'Name Zh'),
			'description'=>Yii::t('Ticket', 'Description'),
			'description_zh'=>Yii::t('Ticket', 'Description Zh'),
			'fee'=>Yii::t('Ticket', 'Fee'),
			'purchase_deadline'=>Yii::t('Ticket', 'Purchase Deadline'),
			'number'=>Yii::t('Ticket', 'Number'),
			'status'=>Yii::t('Ticket', 'Status'),
			'create_time'=>Yii::t('Ticket', 'Create Time'),
			'update_time'=>Yii::t('Ticket', 'Update Time'),
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
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('name_zh', $this->name_zh, true);
		$criteria->compare('description', $this->description, true);
		$criteria->compare('description_zh', $this->description_zh, true);
		$criteria->compare('fee', $this->fee, true);
		$criteria->compare('purchase_deadline', $this->purchase_deadline, true);
		$criteria->compare('number', $this->number, true);
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
	 * @return Ticket the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
