<?php

/**
 * This is the model class for table "region".
 *
 * The followings are the available columns in table 'region':
 * @property integer $id
 * @property string $name
 * @property string $name_zh
 * @property integer $pid
 */
class Region extends ActiveRecord {

	const EARTH_RADIUS = 6378.137;

	public static $HKMCTW = array(2, 3, 4);

	public static function getDistance($lat1, $lng1, $lat2, $lng2) {
		$lat1 = self::toRad($lat1);
		$lat2 = self::toRad($lat2);
		$a = $lat1 - $lat2;
		$b = self::toRad($lng1) - self::toRad($lng2);
		$s = 2 * asin(sqrt(
			pow(sin($a / 2), 2) +
			cos($lat1) * cos($lat2) * pow(sin($b / 2), 2)
		));
		$s = $s * self::EARTH_RADIUS;
		$s = round($s * 10000) / 10;
		return $s;
	}

	public static function toRad($d) {
		return $d * pi() / 180;
	}

	public static function getIconName($country, $iso2) {
		return CHtml::image('https://i.cubingchina.com/flag/' . strtolower($iso2) . '.png', $country, array('class'=>'flag-icon')) . Yii::t('Region', $country);
	}

	//wac stands for Wolrd, Asia and China
	public static function getWACRegions($region = 'China') {
		$regions = array(
			'World'=>Yii::t('Region', 'World'),
			'Asia'=>Yii::t('Region', 'Asia'),
			'China'=>Yii::t('Region', 'China'),
		);
		if (!isset($regions[$region])) {
			$regions[$region] = $region;
		}
		return $regions;
	}

	public static function isValidRegion($region) {
		$regions = self::getWCARegions();
		return $region === 'World' || isset($regions[Yii::t('Region', 'Continents')][$region]) || isset($regions[Yii::t('common', 'Region')][$region]);
	}

	public static function getWCARegions() {
		$countriesKey = Yii::t('common', 'Region');
		$regions = array(
			'World'=>Yii::t('Region', 'World'),
			Yii::t('Region', 'Continents')=>array(
				'Asia'=>Yii::t('Region', 'Asia'),
				'Africa'=>Yii::t('Region', 'Africa'),
				'Europe'=>Yii::t('Region', 'Europe'),
				'North America'=>Yii::t('Region', 'North America'),
				'Oceania'=>Yii::t('Region', 'Oceania'),
				'South America'=>Yii::t('Region', 'South America'),
			),
			$countriesKey=>array(
				'China'=>Yii::t('Region', 'China'),
				'Hong Kong'=>Yii::t('Region', 'Hong Kong'),
				'Macau'=>Yii::t('Region', 'Macau'),
				'Taiwan'=>Yii::t('Region', 'Taiwan'),
			),
		);
		$countries = Countries::getUsedCountries();
		uksort($countries, function($countryA, $countryB) {
			return strcmp(iconv('UTF-8', 'GBK', Yii::t('Region', $countryA)), iconv('UTF-8', 'GBK', Yii::t('Region', $countryB)));
		});
		foreach ($countries as $id=>$country) {
			$regions[$countriesKey][$id] = Yii::t('Region', $country);
		}
		return $regions;
	}

	public static function getRegionById($id) {
		return self::model()->findByPk($id);
	}

	public static function getCountries() {
		$attribute = Yii::app()->controller->getAttributeName('name');
		return CHtml::listData(self::getRegionsByPid(0), 'id', $attribute);
	}

	public static function getProvinces($mainland = true) {
		$attribute = Yii::app()->controller->getAttributeName('name');
		$regions = self::getRegionsByPid(1);
		if (!$mainland) {
			$regions = array_merge($regions, self::getRegionsById(self::$HKMCTW));
		}
		return CHtml::listData($regions, 'id', $attribute);
	}

	public static function getAllCities() {
		$attribute = Yii::app()->controller->getAttributeName('name');
		$cities = Yii::app()->db
			->cache(86400)
			->createCommand()
			->select('*')
			->from('region')
			->where('pid>1')
			->order('id')
			->queryAll();
		$allCities = array();
		foreach ($cities as $city) {
			if (!isset($allCities[$city['pid']])) {
				$allCities[$city['pid']] = array();
			}
			$allCities[$city['pid']][$city['id']] = $city[$attribute];
		}
		return $allCities;
	}

	public static function getRegionIdByName($name) {
		$region = self::model()->findByAttributes(array(
			'name'=>$name,
		));
		if ($region === null) {
			return 0;
		}
		return $region->id;
	}

	public static function getRegionsByPid($pid) {
		return self::model()->findAllByAttributes(array(
			'pid'=>$pid,
		));
	}

	public static function getRegionsById($id) {
		return self::model()->findAllByAttributes(array(
			'id'=>$id,
		));
	}

	public static function isContinent($region) {
		return in_array($region, array('Africa', 'Asia', 'Oceania', 'Europe', 'North America', 'South America'));
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'region';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('pid', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>32),
			array('name_zh', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, name_zh, pid', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'wcaCountry'=>[self::BELONGS_TO, 'Countries', ['name'=>'id']],
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Region', 'ID'),
			'name' => Yii::t('Region', 'Name'),
			'name_zh' => Yii::t('Region', 'Name Zh'),
			'pid' => Yii::t('Region', 'Pid'),
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

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('name_zh',$this->name_zh,true);
		$criteria->compare('pid',$this->pid);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Region the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
