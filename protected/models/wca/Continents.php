<?php

/**
 * This is the model class for table "Continents".
 *
 * The followings are the available columns in table 'Continents':
 * @property string $id
 * @property string $name
 * @property string $recordName
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $zoom
 */
class Continents extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Continents';
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
			array('recordName', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, recordName, latitude, longitude, zoom', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Continents', 'ID'),
			'name' => Yii::t('Continents', 'Name'),
			'recordName' => Yii::t('Continents', 'Record Name'),
			'latitude' => Yii::t('Continents', 'Latitude'),
			'longitude' => Yii::t('Continents', 'Longitude'),
			'zoom' => Yii::t('Continents', 'Zoom'),
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
		$criteria->compare('recordName',$this->recordName,true);
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
	 * @return Continents the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
