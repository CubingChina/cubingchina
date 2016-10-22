<?php

/**
 * This is the model class for table "Countries".
 *
 * The followings are the available columns in table 'Countries':
 * @property string $id
 * @property string $name
 * @property string $continentId
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
		->select('rs.personCountryId, c.name')
		->from('Results rs')
		->leftJoin('Countries c', 'rs.personCountryId=c.id')
		->group('rs.personCountryId')
		->order('rs.personCountryId')
		->queryAll();
		return CHtml::listData($countries, 'personCountryId', 'name');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Countries';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('latitude, longitude, zoom', 'numerical', 'integerOnly'=>true),
			array('id, name, continentId', 'length', 'max'=>50),
			array('iso2', 'length', 'max'=>2),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, continentId, latitude, longitude, zoom, iso2', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'continent'=>array(self::BELONGS_TO, 'Continents', 'continentId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Countries', 'ID'),
			'name' => Yii::t('Countries', 'Name'),
			'continentId' => Yii::t('Countries', 'Continent'),
			'latitude' => Yii::t('Countries', 'Latitude'),
			'longitude' => Yii::t('Countries', 'Longitude'),
			'zoom' => Yii::t('Countries', 'Zoom'),
			'iso2' => Yii::t('Countries', 'Iso2'),
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
		$criteria->compare('continentId',$this->continentId,true);
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
	 * @return Countries the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
