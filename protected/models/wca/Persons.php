<?php

/**
 * This is the model class for table "Persons".
 *
 * The followings are the available columns in table 'Persons':
 * @property string $id
 * @property integer $subid
 * @property string $name
 * @property string $countryId
 * @property string $gender
 */
class Persons extends ActiveRecord {

	public static function getGenders() {
		return array(
			'all'=>Yii::t('common', 'All'),
			'female'=>Yii::t('common', 'Female'),
			'male'=>Yii::t('common', 'Male'),
		);
	}

	public static function getPersonNameById($id) {
		$person = self::model()->findByAttributes(array(
			'id'=>$id,
			'subid'=>1,
		));
		if ($person === null) {
			return '';
		}
		return $person->name;
	}

	public static function getLinkById($id) {
		$person = self::model()->findByAttributes(array(
			'id'=>$id,
			'subid'=>1,
		));
		if ($person === null) {
			return '';
		}
		return self::getLinkByNameNId($person->name, $id);
	}

	public static function getLinkByNameNId($name, $id) {
		return CHtml::link($name, array(
			'/results/p',
			'id'=>$id,
		));
	}

	public static function getResults($id) {
		$personRanks = RanksSingle::model()->with(array(
			'average',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id
		), array(
			'order'=>'event.rank ASC',
		));
		$personResults = array();
		$eventId = '';
		$best = $average = PHP_INT_MAX;
		$results = Results::model()->with(array(
			'competition',
			'round',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id
		), array(
			'order'=>'event.rank, competition.year, competition.month, competition.day, round.rank'
		));
		foreach($results as $result) {
			if ($eventId != $result->eventId) {
				//重置各值
				$eventId = $result->eventId;
				$best = $average = PHP_INT_MAX;
				$personResults[$eventId] = array();
			}
			if ($result->best > 0 && $result->best <= $best) {
				$result->newBest = true;
				$best = $result->best;
			}
			if ($result->average > 0 && $result->average <= $average) {
				$result->newAverage = true;
				$average = $result->average;
			}
			$personResults[$eventId][] = $result;
		}
		return array(
			'personRanks'=>$personRanks,
			'personResults'=>call_user_func_array('array_merge', array_map('array_reverse', $personResults)),
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Persons';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('subid', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>80),
			array('countryId', 'length', 'max'=>50),
			array('gender', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, subid, name, countryId, gender', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'country'=>array(self::BELONGS_TO, 'Countries', 'countryId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('Persons', 'ID'),
			'subid'=>Yii::t('Persons', 'Subid'),
			'name'=>Yii::t('Persons', 'Name'),
			'countryId'=>Yii::t('Persons', 'Country'),
			'gender'=>Yii::t('Persons', 'Gender'),
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
		$criteria->compare('subid',$this->subid);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('countryId',$this->countryId,true);
		$criteria->compare('gender',$this->gender,true);

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
	 * @return Persons the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
