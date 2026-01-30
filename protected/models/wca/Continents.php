<?php

/**
 * This is the model class for table "continents".
 *
 * The followings are the available columns in table 'continents':
 * @property string $id
 * @property string $name
 * @property string $record_name
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $zoom
 */
class Continents extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'continents';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('latitude, longitude, zoom', 'numerical', 'integerOnly'=>true),
			array('id, name', 'length', 'max'=>50),
			array('record_name', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, record_name, latitude, longitude, zoom', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return [
			'countries'=>[self::HAS_MANY, 'Countries', 'continent_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('continents', 'ID'),
			'name' => Yii::t('continents', 'Name'),
			'record_name' => Yii::t('continents', 'Record Name'),
			'latitude' => Yii::t('continents', 'Latitude'),
			'longitude' => Yii::t('continents', 'Longitude'),
			'zoom' => Yii::t('continents', 'Zoom'),
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
		$criteria->compare('record_name',$this->record_name,true);
		$criteria->compare('latitude',$this->latitude);
		$criteria->compare('longitude',$this->longitude);
		$criteria->compare('zoom',$this->zoom);

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
	 * @return continents the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
