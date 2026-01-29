<?php

/**
 * This is the model class for table "championships".
 *
 * The followings are the available columns in table 'championships':
 * @property integer $id
 * @property string $competition_id
 * @property string $championship_type
 */
class Championships extends ActiveRecord {

	public static function buildChampionshipPodiums($type) {
		$championships = self::model()->with([
			'competition',
			'iso2s',
			'country',
			'continent',
		])->findAllByAttributes([
			'championship_type'=>$type,
		], [
			'order'=>'competition.year DESC',
		]);
		$podiums = [];
		foreach ($championships as $championship) {
			$podiums = array_merge_recursive($podiums, $championship->getChampionshipPodiums());
		}
		return $podiums;
	}

	public static function getAllTypes() {
		return CHtml::listData(self::model()->findAll([
			'group'=>'championship_type',
		]), 'championship_type', 'championship_type');
	}

	public static function getRegionName($name, $person) {
		switch ($name) {
			case 'world':
				return Yii::t('Region', 'World');
			case 'continent':
				return Yii::t('Region', $person->country->continent->name);
			case 'greater_china':
				return Yii::t('Region', 'Greater China');
			case 'region':
				return Yii::t('Region', $person->country->name);
			default:
				return $name;
		}
	}

	public function getChampionshipPodiums() {
		$countries = [];
		$isWorld = false;
		if ($this->iso2s !== []) {
			$countries = CHtml::listData($this->iso2s, 'id', 'country');
		} elseif ($this->country) {
			$countries = [$this->country];
		} elseif ($this->continent) {
			$countries = $this->continent->countries;
		} elseif ($this->championship_type === 'world') {
			$isWorld = true;
		}
		$countryIds = CHtml::listData($countries, 'id', 'id');
		$podiums = [];
		$events = Events::getNormalEvents() + Events::getDeprecatedEvents();
		foreach ($events as $event_id=>$eventName) {
			$attributes = [
				'competition_id'=>$this->competition_id,
				'event_id'=>"$event_id",
				'round_type_id'=>['c', 'f'],
			];
			if (!$isWorld) {
				$attributes['person_country_id'] = $countryIds;
			}
			//top 10 is enough
			$top10 = Results::model()->findAllByAttributes($attributes, [
				'condition'=>'best>0',
				'order'=>'pos ASC',
				'limit'=>10,
			]);
			if ($top10 === []) {
				continue;
			}
			$pos = 0;
			$lastPos = 0;
			$count = 0;
			foreach ($top10 as $result) {
				$count++;
				if ($result->pos != $lastPos) {
					$pos = $count;
					$lastPos = $result->pos;
					//only top 3
					if ($count > 3) {
						break;
					}
				}
				//the official pos might not be the regional podiums pos
				$result->pos = $pos;
				$podiums[$result->person_id][] = $result;
			}
		}
		return $podiums;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'championships';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, championship_type', 'required'),
			array('id', 'numerical', 'integerOnly'=>true),
			array('competition_id', 'length', 'max'=>32),
			array('championship_type', 'length', 'max'=>191),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, championship_type', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return [
			'competition'=>[self::BELONGS_TO, 'Competitions', 'competition_id'],
			'continent'=>[self::BELONGS_TO, 'Continents', ['championship_type'=>'id']],
			'country'=>[self::BELONGS_TO, 'Countries', ['championship_type'=>'iso2']],
			'iso2s'=>[self::HAS_MANY, 'EligibleCountryIso2sForChampionship', ['championship_type'=>'championship_type']],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'competition_id'=>'Competition',
			'championship_type'=>'Championship Type',
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
		$criteria->compare('competition_id',$this->competition_id,true);
		$criteria->compare('championship_type',$this->championship_type,true);

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
	 * @return championships the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
