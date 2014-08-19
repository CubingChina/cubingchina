<?php

/**
 * This is the model class for table "registration".
 *
 * The followings are the available columns in table 'registration':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property string $events
 * @property string $comments
 * @property string $date
 * @property integer $status
 */
class Registration extends ActiveRecord {
	public $number;

	public static $sortByUserAttribute = false;
	public static $sortByEvent = false;
	public static $sortAttribute = 'number';
	public static $sortDesc = false;

	const UNPAID = 0;
	const PAID = 1;

	const STATUS_WAITING = 0;
	const STATUS_ACCEPTED = 1;

	public static function getHourlyRegistration() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(r.date), "%k") as hour, COUNT(1) AS registration ')
			->from('registration r')
			->leftJoin('user u', 'r.user_id=u.id')
			->where('u.status=' . User::STATUS_NORMAL)
			->group('FROM_UNIXTIME(r.date, "%k")')
			->queryAll();
		return $data;
	}

	public static function getAllStatus() {
		return array(
			self::STATUS_WAITING=>Yii::t('common', 'Pending'), 
			self::STATUS_ACCEPTED=>Yii::t('common', 'Accepted'), 
		);
	}

	public static function getUserRegistration($competitionId, $userId) {
		return self::model()->findByAttributes(array(
			'competition_id'=>$competitionId,
			'user_id'=>$userId,
		));
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function isAccepted() {
		return $this->status == self::STATUS_ACCEPTED;
	}

	public function getEventsString($event) {
		if (in_array($event, $this->events)) {
			return '<span class="fa fa-check"></span>';
		}
		return '';
	}

	public function getRegistrationEvents() {
		$this->competition->formatEvents();
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->events as $event) {
			if (isset($competitionEvents[$event])) {
				$events[] = Yii::t('event', $competitionEvents[$event]);
			}
		}
		return implode(Yii::t('common', ', '), $events);
	}

	public function getRegistrationFee() {
		$this->competition->formatEvents();
		$competitionEvents = $this->competition->events;
		$fees = array();
		foreach ($this->events as $event) {
			if (isset($competitionEvents[$event]) && $competitionEvents[$event]['round'] > 0) {
				$fees[] = $competitionEvents[$event]['fee'];
			}
		}
		if (count($fees) === 0) {
			$fee = $this->competition->entry_fee;
		} elseif (($total = array_sum($fees)) == 0) {
			$fee = $this->competition->entry_fee;
		} else {
			array_unshift($fees, $this->competition->entry_fee);
			$total += $this->competition->entry_fee;
			$fee = implode('+', $fees) . '=' . $total;
		}
		if ($this->paid == self::PAID && $fee > 0) {
			$fee .= Yii::t('common', ' (paid)');
		}
		return $fee;
	}

	public function getAdminColumns() {
		if ($this->competition === null) {
			$columns = array();
		} else {
			$this->competition->formatEvents();
			$columns = $this->competition->getEventsColumns();
			array_splice($columns, 4, 0, array(
				array(
					'name'=>'birthday',
					'header'=>Yii::t('common', 'Birthday'),
					'headerHtmlOptions'=>array(
						'class'=>'header-birthday',
					),
					'type'=>'raw', 
					'value'=>'date("Y-m-d", $data->user->birthday)', 
				)
			));
		}
		$userLink = Yii::app()->user->checkAccess(User::ROLE_ADMINISTRATOR)
			? 'CHtml::link($data->user->getCompetitionName(), array("/board/user/edit", "id"=>$data->user_id))'
			: '$data->user->getWcaLink()';
		$columns = array_merge(array(
			array(
				'header'=>'操作',
				'headerHtmlOptions'=>array(
					'class'=>'header-operation',
				),
				'type'=>'raw',
				'value'=>'$data->operationButton',
			),
			array(
				'name'=>'user_id',
				'header'=>'用户ID',
				'value'=>'$data->user_id', 
			),
		), $columns, array(
			array(
				'name'=>'email',
				'header'=>Yii::t('common', 'Email'),
				'headerHtmlOptions'=>array(
					'class'=>'header-email',
				),
				'type'=>'raw', 
				'value'=>'$data->user->getEmailLink()', 
			),
			array(
				'name'=>'mobile',
				'header'=>Yii::t('common', 'Mobile Number'),
				'headerHtmlOptions'=>array(
					'class'=>'header-mobile',
				),
				'type'=>'raw', 
				'value'=>'$data->user->mobile', 
			),
			array(
				'name'=>'fee',
				'header'=>Yii::t('common', 'Fee'),
				'headerHtmlOptions'=>array(
					// 'class'=>'header-mobile',
				),
				'type'=>'raw', 
				'value'=>'$data->getRegistrationFee()', 
			),
			array(
				'headerHtmlOptions'=>array(
					'class'=>'header-comments',
				),
				'filter'=>false,
				'type'=>'raw',
				'value'=>'$data->getCommentsButton()',
			),
		));
		return $columns;
	}

	public function getCommentsButton() {
		if ($this->comments !== '') {
			return CHtml::tag('button', array(
				'class'=>'btn btn-xs btn-square btn-purple view-comments',
				'data-comments'=>$this->comments,
				'data-toggle'=>'modal',
				'data-target'=>'#comments-modal',
			), '查看');
		}
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑', array('/board/registration/edit', 'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		switch ($this->status) {
			case self::STATUS_WAITING:
				$buttons[] = CHtml::link('通过', array('/board/registration/show', 'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
				break;
			case self::STATUS_ACCEPTED:
				$buttons[] = CHtml::link('取消', array('/board/registration/hide', 'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
				break;
		}
		switch ($this->paid) {
			case self::UNPAID:
				$buttons[] = CHtml::link('付了', array('/board/registration/paid', 'id'=>$this->id), array('class'=>'btn btn-xs btn-orange btn-square'));
				break;
			case self::PAID:
				$buttons[] = CHtml::link('没付', array('/board/registration/unpaid', 'id'=>$this->id), array('class'=>'btn btn-xs btn-purple btn-square'));
				break;
		}
		return implode(' ', $buttons);
	}

	public function handleEvents() {
		if ($this->events !== null) {
			$this->events = json_encode($this->events);
		}
	}

	public function formatEvents() {
		if (is_array($this->events)) {
			return;
		}
		$temp = json_decode($this->events, true);
		if ($temp === null) {
			$temp = array();
		}
		$this->events = $temp;
	}

	protected function afterFind() {
		$this->formatEvents();
	}

	protected function beforeValidate() {
		$this->handleEvents();
		return parent::beforeValidate();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'registration';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id, events, date', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, date', 'length', 'max'=>10),
			array('events', 'length', 'max'=>512),
			array('comments', 'length', 'max'=>2048),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, user_id, events, comments, date, status', 'safe', 'on'=>'search'),
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
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Registration', 'ID'),
			'competition_id' => Yii::t('Registration', 'Competition'),
			'user_id' => Yii::t('Registration', 'User'),
			'events' => Yii::t('Registration', 'Events'),
			'comments' => Yii::t('Registration', 'Additional Comments'),
			'date' => Yii::t('Registration', 'Registration Date'),
			'status' => Yii::t('Registration', 'Status'),
			'fee' => Yii::t('Registration', 'Fee (CNY)'),
		);
	}

	public function getSort($columns = array()) {
		$sort = array(
			'attributes'=>array(),
			'sortVar'=>'sort',
		);
		foreach ($columns as $column) {
			if (isset($column['name'])) {
				$sort['attributes'][$column['name']] = $column['name'];
			}
		}
		return $sort;
	}

	private function sortRegistration($rA, $rB) {
		$attribute = self::$sortAttribute;
		if (self::$sortByUserAttribute === true) {
			if (ctype_digit($rA->user->$attribute)) {
				$temp = $rA->user->$attribute - $rB->user->$attribute;
			} else {
				$temp = strcmp($rA->user->$attribute, $rB->user->$attribute);
			}
		} elseif (self::$sortByEvent === true) {
			$temp = in_array($attribute, $rB->events) - in_array($attribute, $rA->events);
		} else {
			$temp = $rA->$attribute - $rB->$attribute;
		}
		if ($temp == 0) {
			return $rA->number - $rB->number;
		} else {
			return $temp;
		}
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
	public function search(&$columns = array()) {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->order = 't.date';
		$criteria->with = array('user', 'user.country', 'user.province', 'user.city', 'competition');

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.competition_id',$this->competition_id);
		$criteria->compare('t.user_id',$this->user_id);
		$criteria->compare('t.events',$this->events,true);
		$criteria->compare('t.comments',$this->comments,true);
		$criteria->compare('t.date',$this->date,true);
		$criteria->compare('t.status',$this->status);
		$criteria->compare('user.status', User::STATUS_NORMAL);
		$registrations = $this->findAll($criteria);
		$number = 1;
		$statistics = array();
		$statistics['name'] = 0;
		foreach ($registrations as $registration) {
			if ($registration->isAccepted()) {
				$registration->number = $number++;
			}
			$statistics['name']++;
			foreach ($registration->events as $event) {
				if (!isset($statistics[$event])) {
					$statistics[$event] = 0;
				}
				$statistics[$event]++;
			}
		}
		foreach ($columns as $key=>$column) {
			if (isset($column['name']) && isset($statistics[$column['name']])) {
				$columns[$key]['footer'] = $statistics[$column['name']];
			}
		}
		$sort = Yii::app()->controller->sGet('sort');
		$sort = explode('.', $sort);
		if (isset($sort[1]) && $sort[1] === 'desc') {
			self::$sortDesc = true;
		}
		$sort = $sort[0];
		if ($sort !== '') {
			switch ($sort) {
				case 'name':
				case 'gender':
				case 'region_id':
				case 'birthday':
				case 'email':
				case 'mobile':
					self::$sortByUserAttribute = true;
				case 'number':
				case 'user_id':
					self::$sortAttribute = $sort;
					break;
				
				default:
					self::$sortByEvent = true;
					self::$sortAttribute = $sort;
					break;
			}
			usort($registrations, array($this, 'sortRegistration'));
			if (self::$sortDesc === true) {
				$registrations = array_reverse($registrations);
			}
		}

		return new NonSortArrayDataProvider($registrations, array(
			'sort'=>$this->getSort($columns),
			'pagination'=>false,
		));
	}

	public function searchUser() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->order = 't.date DESC';

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.competition_id',$this->competition_id);
		$criteria->compare('t.user_id',$this->user_id);
		$criteria->compare('t.events',$this->events,true);
		$criteria->compare('t.comments',$this->comments,true);
		$criteria->compare('t.date',$this->date,true);
		$criteria->compare('t.status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Registration the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
