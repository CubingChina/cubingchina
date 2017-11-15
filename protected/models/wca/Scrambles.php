<?php

/**
 * This is the model class for table "Scrambles".
 *
 * The followings are the available columns in table 'Scrambles':
 * @property string $scrambleId
 * @property string $competitionId
 * @property string $eventId
 * @property string $roundTypeId
 * @property string $groupId
 * @property integer $isExtra
 * @property integer $scrambleNum
 * @property string $scramble
 */
class Scrambles extends ActiveRecord {

	public function getNum() {
		return ($this->isExtra ? 'Ex' : '') . '#' . $this->scrambleNum;
	}

	public function getFormattedScramble() {
		$scramble = $this->scramble;
		switch ($this->eventId) {
			case '444':
			case '444bf':
			case '555':
			case '555bf':
			case '666':
			case '777':
			case 'minx':
				$scramble = preg_split('{\s+}', $scramble);
				$num = substr($this->eventId, 0, 1);
				if ($num > 0) {
					$scramble = array_map(function($scramble) use($num) {
						return str_pad($scramble, $num > 5 ? 4 : 3, ' ');
					}, $scramble);
				}
				$scramble = array_chunk($scramble, $this->eventId === 'minx' ? 11 : 10);
				$scramble = array_map(function($scramble) {
					return implode(' ', $scramble);
				}, $scramble);
				$scramble = implode('<br>', $scramble);
				break;
			case 'sq1':
				$scramble = explode('/', $scramble);
				$scramble = array_map(function($scramble) {
					$scramble = trim($scramble);
					$temp = explode(',', $scramble);
					if (!isset($temp[1])) {
						return $scramble;
					}
					$first = str_pad(substr($temp[0], 1), 2, ' ', STR_PAD_LEFT);
					$second = str_pad(substr($temp[1], 0, -1), 2, ' ', STR_PAD_LEFT);
					return sprintf('(%s,%s)', $first, $second);
				}, $scramble);
				$scramble = array_chunk($scramble, 5);
				$scramble = array_map(function($scramble) {
					return implode(' / ', $scramble);
				}, $scramble);
				$scramble = implode(' /<br>', $scramble);
				break;
			case 'clock':
				$scramble = explode(' ', $scramble);
				$scramble = array_map(function($scramble) {
					return str_pad($scramble, 5, ' ');
				}, $scramble);
				$scramble = implode(' ', $scramble);
				$pos = strpos($scramble, 'y');
				if ($pos !== false) {
					// $pos = strpos($scramble, ' ', $pos)
					$scramble = substr($scramble, 0, $pos - 1) . '<br>' . substr($scramble, $pos);
				}
				break;
			case '333mbf':
				$scrambles = explode("\n", $scramble);
				$scrambles = array_map(function($scramble) {
					$scramble = explode(' ', $scramble);
					$scramble = array_map(function($scramble) {
						return str_pad($scramble, 2, ' ');
					}, $scramble);
					$scramble = implode(' ', $scramble);
					return $scramble;
				}, $scrambles);
				$scramble = implode('<br>', $scrambles);
				break;
			default:
				$scramble = explode(' ', $scramble);
				$scramble = array_map(function($scramble) {
					return str_pad($scramble, 2, ' ');
				}, $scramble);
				$scramble = implode(' ', $scramble);
				break;
		}
		$scramble = CHtml::tag('pre', array(), $scramble);
		return $scramble;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Scrambles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competitionId, eventId, roundTypeId, groupId, isExtra, scrambleNum, scramble', 'required'),
			array('isExtra, scrambleNum', 'numerical', 'integerOnly'=>true),
			array('scrambleId', 'length', 'max'=>10),
			array('competitionId', 'length', 'max'=>32),
			array('eventId', 'length', 'max'=>6),
			array('roundTypeId', 'length', 'max'=>1),
			array('groupId', 'length', 'max'=>3),
			array('scramble', 'length', 'max'=>500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('scrambleId, competitionId, eventId, roundTypeId, groupId, isExtra, scrambleNum, scramble', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competitions', 'competitionId'),
			'round'=>array(self::BELONGS_TO, 'RoundTypes', 'roundTypeId'),
			'event'=>array(self::BELONGS_TO, 'Events', 'eventId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'scrambleId' => Yii::t('Scrambles', 'Scramble'),
			'competitionId' => Yii::t('Scrambles', 'Competition'),
			'eventId' => Yii::t('Scrambles', 'Event'),
			'roundTypeId' => Yii::t('Scrambles', 'Round'),
			'groupId' => Yii::t('Scrambles', 'Group'),
			'isExtra' => Yii::t('Scrambles', 'Is Extra'),
			'scrambleNum' => Yii::t('Scrambles', 'Scramble Num'),
			'scramble' => Yii::t('Scrambles', 'Scramble'),
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

		$criteria->compare('scrambleId', $this->scrambleId, true);
		$criteria->compare('competitionId', $this->competitionId, true);
		$criteria->compare('eventId', $this->eventId, true);
		$criteria->compare('roundTypeId', $this->roundTypeId, true);
		$criteria->compare('groupId', $this->groupId, true);
		$criteria->compare('isExtra', $this->isExtra);
		$criteria->compare('scrambleNum', $this->scrambleNum);
		$criteria->compare('scramble', $this->scramble, true);

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
	 * @return Scrambles the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
