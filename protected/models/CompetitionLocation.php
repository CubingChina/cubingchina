<?php

/**
 * This is the model class for table "competition_location".
 *
 * The followings are the available columns in table 'competition_location':
 * @property string $id
 * @property string $competition_id
 * @property integer $location_id
 * @property integer $province_id
 * @property integer $city_id
 * @property string $venue
 * @property string $venue_zh
 */
class CompetitionLocation extends ActiveRecord {

	public function getCityName($showDisinct = true) {
		switch (true) {
			case $this->country_id > 3:
				return $this->getAttributeValue('city_name');
			case $this->country_id > 1:
				return $this->country->getAttributeValue('name');
			case $showDisinct && in_array($this->province_id, [215, 525, 567, 642]):
				return $this->province->getAttributeValue('name');
			default:
				return $this->city ? $this->city->getAttributeValue('name') : $this->getAttributeValue('venue');
		}
	}

	public function getDelegateInfo() {
		if ($this->delegate) {
			return CHtml::mailto(Html::fontAwesome('envelope', 'a') . $this->delegate->getAttributeValue('name', true), $this->delegate->email);
		} else {
			return $this->delegate_text;
		}
	}

	public function getFeeInfo() {
		return $this->country_id == 1 ? $this->competition->getEventFee('entry') : $this->fee;
	}

	public function getFullAddress($includeVenue = true) {
		$isCN = Yii::app()->controller->isCN;
		$country = $this->country ? Yii::t('Region', $this->country->getAttributeValue('name')) : '';
		$province = $this->province ? $this->province->getAttributeValue('name') : '';
		$city = $this->getCityName(false);
		if ($city == $province) {
			$city = '';
		}
		if ($city == $country) {
			$city = '';
		}
		if ($isCN) {
			$address = $country . $province . $city;
		} else {
			$address = implode(', ', array_filter([$city, $province, $country]));
		}
		if ($includeVenue) {
			if ($isCN) {
				$address .= $this->getAttributeValue('venue');
			} else {
				$address = $this->getAttributeValue('venue') . ', ' . $address;
			}
		}
		return $address;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competition_location';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id', 'required'),
			array('location_id, country_id, province_id, city_id, delegate_id', 'numerical', 'integerOnly'=>true),
			array('competition_id', 'length', 'max'=>10),
			array('venue, venue_zh, city_name, city_name_zh, delegate_text, fee, longitude, latitude', 'length', 'max'=>512),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, location_id, province_id, city_id, venue, venue_zh', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'country'=>array(self::BELONGS_TO, 'Region', 'country_id'),
			'province'=>array(self::BELONGS_TO, 'Region', 'province_id'),
			'city'=>array(self::BELONGS_TO, 'Region', 'city_id'),
			'delegate'=>array(self::BELONGS_TO, 'User', 'delegate_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('CompetitionLocation', 'ID'),
			'competition_id' => Yii::t('CompetitionLocation', 'Competition'),
			'location_id' => Yii::t('common', 'Competition Site'),
			'province_id' => Yii::t('CompetitionLocation', 'Province'),
			'city_id' => Yii::t('CompetitionLocation', 'City'),
			'venue' => Yii::t('CompetitionLocation', 'Venue'),
			'venue_zh' => Yii::t('CompetitionLocation', 'Venue Zh'),
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
		$criteria->compare('competition_id',$this->competition_id,true);
		$criteria->compare('location_id',$this->location_id);
		$criteria->compare('province_id',$this->province_id);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('venue',$this->venue,true);
		$criteria->compare('venue_zh',$this->venue_zh,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompetitionLocation the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
