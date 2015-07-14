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
	public $best = -1;

	public static $sortByUserAttribute = false;
	public static $sortByEvent = false;
	public static $sortAttribute = 'number';
	public static $sortDesc = false;

	const UNPAID = 0;
	const PAID = 1;

	const STATUS_WAITING = 0;
	const STATUS_ACCEPTED = 1;

	public static function getDailyRegistration() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(r.date), "%Y-%m-%d") as day, COUNT(1) AS registration')
			->from('registration r')
			->leftJoin('user u', 'r.user_id=u.id')
			->where('u.status=' . User::STATUS_NORMAL)
			->group('FROM_UNIXTIME(r.date, "%Y-%m-%d")')
			->queryAll();
		return $data;
	}

	public static function getHourlyRegistration() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(r.date), "%k") as hour, COUNT(1) AS registration')
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

	public function isPaid() {
		return $this->paid == self::PAID;
	}

	public function getEventsString($event) {
		$str = '';
		if (in_array($event, $this->events)) {
			$str = '<span class="fa fa-check"></span>';
			if ($this->best > 0 && self::$sortAttribute === $event) {
				$str .= Results::formatTime($this->best, $event);
			}
		}
		return $str;
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
		$fee = $this->getTotalFee();
		if ($this->isPaid() && $fee > 0) {
			$fee .= Yii::t('common', ' (paid)');
		} elseif ($fee > 0) {
			$fee .= CHtml::link(Yii::t('common', 'Pay'), array(
				'/pay/registration',
				'id'=>$this->id,
			), array(
				'class'=>'btn btn-xs btn-theme',
			));
		}
		return $fee;
	}

	public function getTotalFee() {
		$this->competition->formatEvents();
		$competitionEvents = $this->competition->events;
		$fees = array();
		foreach ($this->events as $event) {
			if (isset($competitionEvents[$event]) && $competitionEvents[$event]['round'] > 0) {
				$fees[] = $competitionEvents[$event]['fee'];
			}
		}
		return $this->competition->entry_fee + array_sum($fees);
	}

	public function getLocation() {
		return CompetitionLocation::model()->with('province', 'city')->findByAttributes(array(
			'competition_id'=>$this->competition_id,
			'location_id'=>$this->location_id,
		));
	}

	public function getNoticeColumns($model) {
		if ($this->competition === null) {
			$columns = array();
		} else {
			$this->competition->formatEvents();
			$columns = $this->competition->getEventsColumns(true);
		}
		$modelName = get_class($model);
		$userLink = Yii::app()->user->checkAccess(User::ROLE_ADMINISTRATOR)
			? 'CHtml::link($data->user->getCompetitionName(), array("/board/user/edit", "id"=>$data->user_id))'
			: '$data->user->getWcaLink()';
		$columns = array_merge(array(
			array(
				'name'=>'email',
				'header'=>Yii::t('common', 'Email'),
				'headerHtmlOptions'=>array(
					'class'=>'header-email',
				),
				'type'=>'raw', 
				'value'=>"CHtml::label(CHtml::checkBox('{$modelName}[competitors][]', \$data->isAccepted(), array(
					'class'=>implode(' ', array_map(function(\$a) {
						return 'event-' . \$a;
					}, \$data->events)) . ' competitor',
					'value'=>\$data->user->email,
					'data-accepted'=>intval(\$data->isAccepted()),
				)) . ' ' . \$data->user->email, false, array(
					'class'=>'checkbox',
				))", 
			),
		), $columns);
		return $columns;
	}

	public function getAdminColumns() {
		if ($this->competition === null) {
			$columns = array();
		} else {
			$this->competition->formatEvents();
			$columns = $this->competition->getEventsColumns(true);
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
		$isAdmin = Yii::app()->user->checkAccess(User::ROLE_ADMINISTRATOR);
		$userLink = $isAdmin
			? 'CHtml::link($data->user->getCompetitionName(), array("/board/user/edit", "id"=>$data->user_id))'
			: '$data->user->getWcaLink()';
		$ipColumn = $isAdmin ? array(
			array(
				'name'=>'ip',
				'type'=>'raw',
				'value'=>'$data->getRegIpDisplay()',
			),
		) : array();
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
				'type'=>'raw', 
				'value'=>'$data->getTotalFee() . ($data->isPaid() ? Yii::t("common", " (paid)") : "")', 
			),
			array(
				'name'=>'comment',
				'headerHtmlOptions'=>array(
					'class'=>'header-comments',
				),
				'filter'=>false,
				'type'=>'raw',
				'value'=>'$data->getCommentsButton()',
			),
			array(
				'name'=>'date',
				'header'=>Yii::t('Registration', 'Registration Date'),
				'type'=>'raw', 
				'value'=>'date("Y-m-d H:i:s", $data->date)', 
			),
		), $ipColumn);
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
		$buttons[] = CHtml::checkBox('paid', $this->paid == self::PAID, array(
			'class'=>'paid tips',
			'data-toggle'=>'tooltip',
			'data-placement'=>'top',
			'title'=>'是否支付报名费',
			'data-checked-url'=>CHtml::normalizeUrl(array('/board/registration/paid', 'id'=>$this->id)),
			'data-unchecked-url'=>CHtml::normalizeUrl(array('/board/registration/unpaid', 'id'=>$this->id)),
		));
		return implode(' ', $buttons);
	}

	public function getUserNumber() {
		if ($this->isAccepted()) {
			return self::model()->countByAttributes(array(
				'competition_id'=>$this->competition_id,
				'status'=>self::STATUS_ACCEPTED,
			), array(
				'condition'=>'date<=' . $this->date,
			));
		} else {
			return '-';
		}
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
			array('location_id, competition_id, user_id, events, date', 'required'),
			array('location_id, status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, date', 'length', 'max'=>10),
			array('events', 'length', 'max'=>512),
			array('comments', 'length', 'max'=>2048),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, location_id, user_id, events, comments, date, status', 'safe', 'on'=>'search'),
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
			'pay'=>array(self::HAS_ONE, 'Pay', 'type_id', 'on'=>'pay.type=' . Pay::TYPE_REGISTRATION),
			// 'location'=>array(self::HAS_ONE, 'CompetitionLocation', '', 'on'=>'t.competition_id=location.competition_id AND t.location_id=location.location_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Registration', 'ID'),
			'competition_id' => Yii::t('Registration', 'Competition'),
			'location_id' => Yii::t('common', 'Competition Site'),
			'user_id' => Yii::t('Registration', 'User'),
			'events' => Yii::t('Registration', 'Events'),
			'comments' => Yii::t('Registration', 'Additional Comments'),
			'ip' => 'IP',
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
		if ($attribute === 'country_id') {
			$temp = $rA->user->country_id - $rB->user->country_id;
			if ($temp == 0) {
				$temp = $rA->user->province_id - $rB->user->province_id;
			}
			if ($temp == 0) {
				$temp = $rA->user->city_id - $rB->user->city_id;
			}
		} elseif (self::$sortByUserAttribute === true) {
			if (is_numeric($rA->user->$attribute)) {
				$temp = $rA->user->$attribute - $rB->user->$attribute;
			} else {
				$temp = strcmp($rA->user->$attribute, $rB->user->$attribute);
			}
		} elseif (self::$sortByEvent === true) {
			$temp = in_array($attribute, $rB->events) - in_array($attribute, $rA->events);
			if ($temp == 0) {
				if ($rA->best > 0 && $rB->best > 0) {
					$temp = $rA->best - $rB->best;
					if (self::$sortDesc === true) {
						$temp = -$temp;
					}
				} elseif ($rA->best > 0) {
					$temp = -1;
				} elseif ($rB->best > 0) {
					$temp = 1;
				} else {
					$temp = 0;
				}
			}
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
		$statistics['number'] = 0;
		$statistics['new'] = 0;
		$statistics['paid'] = 0;
		$statistics['unpaid'] = 0;
		$statistics[User::GENDER_MALE] = 0;
		$statistics[User::GENDER_FEMALE] = 0;

		//detect sort attribute
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
				case 'country_id':
				case 'birthday':
				case 'email':
				case 'mobile':
					self::$sortByUserAttribute = true;
				case 'number':
				case 'user_id':
				case 'location_id':
					self::$sortAttribute = $sort;
					break;
				
				default:
					self::$sortByEvent = true;
					self::$sortAttribute = $sort;
					break;
			}
		}

		$wcaIds = array();
		foreach ($registrations as $registration) {
			if ($registration->isAccepted()) {
				$registration->number = $number++;
			}
			$statistics['number']++;
			$statistics[$registration->user->gender]++;
			if ($registration->user->wcaid === '') {
				$statistics['new']++;
			}
			foreach ($registration->events as $event) {
				if (!isset($statistics[$event])) {
					$statistics[$event] = 0;
				}
				$statistics[$event]++;
			}
			$fee = $registration->getTotalFee();
			if ($registration->isPaid()) {
				$statistics['paid'] += $fee;
			} else {
				$statistics['unpaid'] += $fee;
			}
			//store wcaids
			if ($registration->user->wcaid) {
				$wcaIds[$registration->user->wcaid] = $registration;
			}
		}
		if (self::$sortByEvent === true && !empty($wcaIds)) {
			switch ($sort) {
				case '333bf':
				case '444bf':
				case '555bf':
				case '333mbf':
					$modelName = 'RanksSingle';
					break;
				default:
					$modelName = 'RanksAverage';
					break;
			}
			$results = $modelName::model()->findAllByAttributes(array(
				'eventId'=>$sort,
				'personId'=>array_keys($wcaIds),
			));
			foreach ($results as $result) {
				$wcaIds[$result->personId]->best = $result->best;
			}
		}
		$statistics['gender'] = $statistics[User::GENDER_MALE] . '/' . $statistics[User::GENDER_FEMALE];
		$statistics['old'] = $statistics['number'] - $statistics['new'];
		$statistics['name'] = $statistics['new'] . '/' . $statistics['old'];
		$statistics['fee'] = $statistics['paid'] . '/' . $statistics['unpaid'];
		foreach ($columns as $key=>$column) {
			if (isset($column['name']) && isset($statistics[$column['name']])) {
				$columns[$key]['footer'] = $statistics[$column['name']];
			}
		}
		if ($sort !== '') {
			usort($registrations, array($this, 'sortRegistration'));
			if (self::$sortDesc === true && self::$sortByEvent !== true) {
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
