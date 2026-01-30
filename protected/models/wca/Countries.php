<?php

/**
 * This is the model class for table "countries".
 *
 * The followings are the available columns in table 'countries':
 * @property string $id
 * @property string $name
 * @property string $continent_id
 * @property integer $latitude
 * @property integer $longitude
 * @property integer $zoom
 * @property string $iso2
 */
class Countries extends ActiveRecord {

	public static function getUsedCountries() {
		$countries = $command = Yii::app()->wcaDb
		->cache(86400)
		->createCommand()
		->select('rs.person_country_id, c.name')
		->from('results rs')
		->leftJoin('countries c', 'rs.person_country_id=c.id')
		->group('rs.person_country_id')
		->order('rs.person_country_id')
		->queryAll();
		return CHtml::listData($countries, 'person_country_id', 'name');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'countries';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('latitude, longitude, zoom', 'numerical', 'integerOnly'=>true),
			array('id, name, continent_id', 'length', 'max'=>50),
			array('iso2', 'length', 'max'=>2),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, continent_id, latitude, longitude, zoom, iso2', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'continent'=>array(self::BELONGS_TO, 'Continents', 'continent_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('countries', 'ID'),
			'name' => Yii::t('countries', 'Name'),
			'continent_id' => Yii::t('countries', 'Continent'),
			'latitude' => Yii::t('countries', 'Latitude'),
			'longitude' => Yii::t('countries', 'Longitude'),
			'zoom' => Yii::t('countries', 'Zoom'),
			'iso2' => Yii::t('countries', 'Iso2'),
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
		$criteria->compare('continent_id',$this->continent_id,true);
		$criteria->compare('latitude',$this->latitude);
		$criteria->compare('longitude',$this->longitude);
		$criteria->compare('zoom',$this->zoom);
		$criteria->compare('iso2',$this->iso2,true);

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
	 * @return countries the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
