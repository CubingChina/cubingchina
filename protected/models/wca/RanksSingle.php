<?php

/**
 * This is the model class for table "ranks_single".
 *
 * The followings are the available columns in table 'ranks_single':
 * @property integer $id
 * @property string $person_id
 * @property string $event_id
 * @property integer $best
 * @property integer $world_rank
 * @property integer $continent_rank
 * @property integer $country_rank
 */
class RanksSingle extends ActiveRecord {
	public $medals = array(
		'gold'=>0,
		'silver'=>0,
		'bronze'=>0,
	);

	//获取average数据
	public function average($attribute) {
		if($this->average == null) {
			return '';
		}
		if($attribute == 'best') {
			return CHtml::link(Results::formatTime($this->average->$attribute, $this->event_id), array(
				'/results/rankings',
				'event'=>$this->event_id,
				'type'=>'average',
				'region'=>$this->person->country_id,
			));
		}
		return $this->average->getRank($attribute);
	}

	public function getRank($attribute) {
		if ($this->$attribute <= 0) {
			return '-';
		}
		if ($this->$attribute <= 10) {
			return CHtml::tag('span', array('class'=>'top10'), $this->$attribute);
		}
		return $this->$attribute;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ranks_single';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('best, world_rank, continent_rank, country_rank', 'numerical', 'integerOnly'=>true),
			array('person_id', 'length', 'max'=>10),
			array('event_id', 'length', 'max'=>6),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, person_id, event_id, best, world_rank, continent_rank, country_rank', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'person'=>array(self::BELONGS_TO, 'Persons', 'person_id', 'on'=>'person.sub_id=1'),
			'event'=>array(self::BELONGS_TO, 'Events', 'event_id'),
			'average'=>array(self::BELONGS_TO, 'RanksAverage', array(
				'person_id'=>'person_id',
				'event_id'=>'event_id',
			)),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('ranks_single', 'ID'),
			'person_id' => Yii::t('ranks_single', 'Person'),
			'event_id' => Yii::t('ranks_single', 'Event'),
			'best' => Yii::t('ranks_single', 'Best'),
			'world_rank' => Yii::t('ranks_single', 'World Rank'),
			'continent_rank' => Yii::t('ranks_single', 'Continent Rank'),
			'country_rank' => Yii::t('ranks_single', 'Country Rank'),
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
		$criteria->compare('person_id',$this->person_id,true);
		$criteria->compare('event_id',$this->event_id,true);
		$criteria->compare('best',$this->best);
		$criteria->compare('world_rank',$this->world_rank);
		$criteria->compare('continent_rank',$this->continent_rank);
		$criteria->compare('country_rank',$this->country_rank);

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
	 * @return ranks_single the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
