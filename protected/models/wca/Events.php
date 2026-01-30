<?php

/**
 * This is the model class for table "events".
 *
 * The followings are the available columns in table 'events':
 * @property string $id
 * @property string $name
 * @property integer $rank
 * @property string $format
 * @property string $cell_name
 */
class Events extends ActiveRecord {
	private static $_allEvents;
	private static $_normalEvents;
	private static $_deprecatedEvents;
	private static $_specialEventNames = array(
		'pyram'=>'pyra',
		'minx'=>'mega',
		'333oh'=>'3oh',
		'333bf'=>'3bld',
		'333mbf'=>'3multi',
		'333ft'=>'3feet',
		'444bf'=>'4bld',
		'555bf'=>'5bld',
	);
	private static $_defaultExportFormats = array(
		'333'=>'average5s',
		'444'=>'average5m',
		'555'=>'average5m',
		'222'=>'average5s',
		'333bf'=>'best3m',
		'333oh'=>'average5s',
		'333fm'=>'mean3n',
		'333ft'=>'mean3m',
		'minx'=>'average5m',
		'pyram'=>'average5s',
		'sq1'=>'average5s',
		'clock'=>'average5s',
		'skewb'=>'average5s',
		'666'=>'mean3m',
		'777'=>'mean3m',
		'444bf'=>'best3m',
		'555bf'=>'best3m',
		'333mbf'=>'multibf1',
		'default'=>'average5s',
	);

	public static function getAllExportFormats() {
		$formats = array(
			'average5s',
			'average5m',
			'mean3s',
			'mean3m',
			'mean3n',
			'best1m',
			'best2m',
			'best3m',
			'best1n',
			'best2n',
			'multibf1',
			'multibf2',
			'best1s',
			'best2s',
			'best3s',
		);
		return array_combine($formats, $formats);
	}

	public static function getDefaultExportFormat($event) {
		return isset(self::$_defaultExportFormats[$event]) ? self::$_defaultExportFormats[$event] : self::$_defaultExportFormats['default'];
	}

	public static function getExportFormat($event, $format) {
		if ($event === '333mbf') {
			return 'multibf' . $format;
		}
		if ($event === '333fm') {
			if ($format == 'm') {
				return 'mean3n';
			} else {
				return 'best' . $format . 'n';
			}
		}
		switch ($format) {
			case '1':
			case '2':
			case '3':
				return 'best' . $format . 's';
			case 'm':
				return 'mean3s';
			default:
				return 'average5s';
		}
	}

	public static function getColumnName($event) {
		if (isset(self::$_specialEventNames[$event])) {
			$event = self::$_specialEventNames[$event];
		}
		return ucfirst($event);
	}

	public static function getEventName($event) {
		if (self::$_allEvents === null) {
			self::$_allEvents = self::getScheduleEvents() + self::getDeprecatedEvents();
		}
		return isset(self::$_allEvents[$event]) ? self::$_allEvents[$event] : $event;
	}

	public static function getFullEventName($event) {
		if (self::isCustomEvent($event)) {
			return CustomEvent::getFullEventName($event);
		}
		return Yii::t('event', self::getEventName($event));
	}

	public static function getFullEventNameWithIcon($event, $name = null) {
		if ($name === null) {
			$name = self::getFullEventName($event);
		}
		if (self::isCustomEvent($event) && !CustomEvent::hasIcon($event)) {
			return CustomEvent::getFullEventName($event);
		}
		return self::getEventIcon($event) . ' ' . $name;
	}

	public static function getShortNameWithIcon($event) {
		switch (Yii::app()->language) {
			case 'zh_cn':
				return self::getFullEventNameWithIcon($event, $event === 'submission' ? '交魔方' : null);
			case 'en':
				return self::getFullEventNameWithIcon($event, ucfirst($event));
			case 'zh_tw':
				Yii::app()->language = 'zh_cn';
				$name = self::getFullEventNameWithIcon($event, $event === 'submission' ? '交方块' : null);
				Yii::app()->language = 'zh_tw';
				return Yii::app()->controller->translateTWInNeed($name);
		}
	}

	public static function getEventIcon($event) {
		$name = self::getFullEventName($event);
		$class = ['event-icon', 'event-icon-' . $event];
		if (self::isCustomEvent($event) && !CustomEvent::hasIcon($event)) {
			$class[] = 'event-icon-custom';
		}
		return CHtml::tag('i', [
			'class'=>implode(' ', $class),
			'title'=>$name,
		], '');
	}

	public static function getScheduleEvents() {
		return self::getOnlyScheduleEvents() + self::getAllEvents();
	}

	public static function getOnlyScheduleEvents() {
		return array(
			'registration'=>'Registration',
			'intro'=>'Opening Intro',
			'lunch'=>'Lunch',
			'break'=>'Break',
			'lucky'=>'Lucky Draw',
			'ceremony'=>'Award Ceremony',
			'submission'=>'3x3x3 Multi-Blind Puzzle Submission',
			'333bfcheck'=>'3x3x3 Blindfolded Check-in',
			'444bfcheck'=>'4x4x4 Blindfolded Check-in',
			'555bfcheck'=>'5x5x5 Blindfolded Check-in',
		);
	}

	public static function getAllEvents() {
		return self::getNormalEvents() + self::getOtherEvents();
	}

	public static function getOtherEvents() {
		return [
			'magic'=>'Magic',
			'mmagic'=>'Master Magic',
			'stack'=>'Sport Stacking',
			'funny'=>'Funny Event',
		] + self::getCustomEvents();
	}

	public static function getCustomEvents() {
		return CHtml::listData(CustomEvent::getAllEvents(), 'id', 'name');
	}

	public static function getNormalEvents() {
		if (self::$_normalEvents !== null) {
			return self::$_normalEvents;
		}
		$events = self::model()->cache(86500 * 7)->findAll(array(
			'condition'=>'`rank`<900',
			'order'=>'`rank`',
		));
		$events = CHtml::listData($events, 'id', 'name');
		return self::$_normalEvents = $events;
	}

	public static function getNormalTranslatedEvents() {
		$events = self::getNormalEvents();
		foreach ($events as $event_id=>$eventName) {
			$events[$event_id] = Yii::t('event', $eventName);
		}
		return $events;
	}

	public static function getDeprecatedEvents() {
		if (self::$_deprecatedEvents !== null) {
			return self::$_deprecatedEvents;
		}
		$events = self::model()->cache(86500 * 7)->findAll(array(
			'condition'=>'`rank`>=900 AND `rank`<1000',
			'order'=>'`rank`',
		));
		$events = CHtml::listData($events, 'id', 'name');
		return self::$_deprecatedEvents = $events;
	}

	public static function isCustomEvent($event) {
		return array_key_exists($event, self::getCustomEvents());
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'events';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('rank', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>6),
			array('name', 'length', 'max'=>54),
			array('format', 'length', 'max'=>10),
			array('cell_name', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, rank, format, cell_name', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('events', 'ID'),
			'name' => Yii::t('events', 'Name'),
			'rank' => Yii::t('events', 'Rank'),
			'format' => Yii::t('events', 'Format'),
			'cell_name' => Yii::t('events', 'Cell Name'),
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('rank',$this->rank);
		$criteria->compare('format',$this->format,true);
		$criteria->compare('cell_name',$this->cell_name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection() {
		return Yii::app()->wcaDb;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return events the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
