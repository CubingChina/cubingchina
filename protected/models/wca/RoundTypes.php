<?php

/**
 * This is the model class for table "rounds".
 *
 * The followings are the available columns in table 'rounds':
 * @property string $id
 * @property integer $rank
 * @property string $name
 * @property string $cell_name
 */
class RoundTypes extends ActiveRecord {
	private static $_allRoundTypes;
	public static function getFullRoundName($round) {
		if (self::$_allRoundTypes === null) {
			self::$_allRoundTypes = CHtml::listData(self::model()->cache(86400 * 7)->findAll(), 'id', 'cell_name');
		}
		return isset(self::$_allRoundTypes[$round]) ? self::$_allRoundTypes[$round] : $round;
	}

	public static function getAllRoundTypes() {
		$rounds = self::model()->cache(86400 * 7)->findAll(array(
			'condition'=>'`rank`<900',
			'order'=>'`rank`',
		));
		$rounds = CHtml::listData($rounds, 'id', 'cell_name');
		return $rounds;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'round_types';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('rank', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>1),
			array('name', 'length', 'max'=>50),
			array('cell_name', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, rank, name, cell_name', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('round_types', 'ID'),
			'rank' => Yii::t('round_types', 'Rank'),
			'name' => Yii::t('round_types', 'Name'),
			'cell_name' => Yii::t('round_types', 'Cell Name'),
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
		$criteria->compare('rank',$this->rank);
		$criteria->compare('name',$this->name,true);
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
	 * @return round_types the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
