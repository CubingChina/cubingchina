<?php

/**
 * This is the model class for table "WcaPersons".
 *
 * The followings are the available columns in table 'Persons':
 * @property string $id
 * @property integer $subId
 * @property string $name
 * @property string $countryId
 * @property string $gender
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property string $comments
 */
class WcaPersons extends ActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return WcaPersons the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
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
			array('subId, year, month, day', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>80),
			array('countryId', 'length', 'max'=>50),
			array('gender', 'length', 'max'=>1),
			array('comments', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, subId, name, countryId, gender, year, month, day, comments', 'safe', 'on'=>'search'),
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
			'id'=>'ID',
			'subId'=>'Sub',
			'name'=>'Name',
			'countryId'=>'Country',
			'gender'=>'Gender',
			'year'=>'Year',
			'month'=>'Month',
			'day'=>'Day',
			'comments'=>'Comments',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('subId', $this->subId);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('countryId', $this->countryId, true);
		$criteria->compare('gender', $this->gender, true);
		$criteria->compare('year', $this->year);
		$criteria->compare('month', $this->month);
		$criteria->compare('day', $this->day);
		$criteria->compare('comments', $this->comments, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}