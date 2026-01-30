<?php

/**
 * This is the model class for table "scrambles".
 *
 * The followings are the available columns in table 'scrambles':
 * @property string $scrambleId
 * @property string $competition_id
 * @property string $event_id
 * @property string $round_type_id
 * @property string $group_id
 * @property integer $is_extra
 * @property integer $scramble_num
 * @property string $scramble
 */
class Scrambles extends ActiveRecord {

	public function getNum() {
		return ($this->is_extra ? 'Ex' : '') . '#' . $this->scramble_num;
	}

	public function getFormattedScramble() {
		$scramble = $this->scramble;
		switch ($this->event_id) {
			case '444':
			case '444bf':
			case '555':
			case '555bf':
			case '666':
			case '777':
			case 'minx':
				$scramble = preg_split('{\s+}', $scramble);
				$num = substr($this->event_id, 0, 1);
				if ($num > 0) {
					$scramble = array_map(function($scramble) use($num) {
						return str_pad($scramble, $num > 5 ? 4 : 3, ' ');
					}, $scramble);
				}
				$scramble = array_chunk($scramble, $this->event_id === 'minx' ? 11 : 10);
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
		return 'scrambles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, event_id, round_type_id, group_id, is_extra, scramble_num, scramble', 'required'),
			array('is_extra, scramble_num', 'numerical', 'integerOnly'=>true),
			array('scrambleId', 'length', 'max'=>10),
			array('competition_id', 'length', 'max'=>32),
			array('event_id', 'length', 'max'=>6),
			array('round_type_id', 'length', 'max'=>1),
			array('group_id', 'length', 'max'=>3),
			array('scramble', 'length', 'max'=>500),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('scrambleId, competition_id, event_id, round_type_id, group_id, is_extra, scramble_num, scramble', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competitions', 'competition_id'),
			'round'=>array(self::BELONGS_TO, 'RoundTypes', 'round_type_id'),
			'event'=>array(self::BELONGS_TO, 'Events', 'event_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'scrambleId' => Yii::t('scrambles', 'Scramble'),
			'competition_id' => Yii::t('scrambles', 'Competition'),
			'event_id' => Yii::t('scrambles', 'Event'),
			'round_type_id' => Yii::t('scrambles', 'Round'),
			'group_id' => Yii::t('scrambles', 'Group'),
			'is_extra' => Yii::t('scrambles', 'Is Extra'),
			'scramble_num' => Yii::t('scrambles', 'Scramble Num'),
			'scramble' => Yii::t('scrambles', 'Scramble'),
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
		$criteria->compare('competition_id', $this->competition_id, true);
		$criteria->compare('event_id', $this->event_id, true);
		$criteria->compare('round_type_id', $this->round_type_id, true);
		$criteria->compare('group_id', $this->group_id, true);
		$criteria->compare('is_extra', $this->is_extra);
		$criteria->compare('scramble_num', $this->scramble_num);
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
	 * @return scrambles the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
