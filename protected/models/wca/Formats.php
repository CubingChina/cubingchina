<?php

/**
 * This is the model class for table "Formats".
 *
 * The followings are the available columns in table 'Formats':
 * @property string $id
 * @property string $name
 */
class Formats extends ActiveRecord {
	private static $_allFormats;
	public static function getFullFormatName($format) {
		if (self::$_allFormats === null) {
			self::$_allFormats = self::getAllFormats();
		}
		return isset(self::$_allFormats[$format]) ? self::$_allFormats[$format] : $format;
	}

	public static function getAllFormats() {
		return array(
			'a'=>'Average of 5',
			'2/a'=>'Best of 2/Average of 5',
			'3'=>'Best of 3',
			'm'=>'Mean of 3',
			'1/m'=>'Best of 1/Mean of 3',
			'1'=>'Best of 1',
			'2'=>'Best of 2',
		);
	}
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Formats';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id', 'length', 'max'=>1),
			array('name', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Formats', 'ID'),
			'name' => Yii::t('Formats', 'Name'),
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
	 * @return Formats the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
