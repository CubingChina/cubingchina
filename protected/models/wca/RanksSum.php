<?php

/**
 * This is the model class for table "SumOfRanks".
 *
 * The followings are the available columns in table 'SumOfRanks':
 * @property string $id
 * @property string $person_id
 * @property string $country_id
 * @property string $continent_id
 * @property string $type
 * @property integer $country_rank
 * @property integer $continent_rank
 * @property integer $world_rank
 */
class RanksSum extends ActiveRecord {
	private $_ranks;

	public function getRanks() {
		if ($this->_ranks !== null) {
			return $this->_ranks;
		}
		$this->_ranks = array();
		$this->_ranks['NR'] = self::model()->countByAttributes(array(
			'type'=>$this->type,
			'country_id'=>$this->country_id,
		), array(
			'condition'=>'country_rank<' . $this->country_rank,
		)) + 1;
		$this->_ranks['CR'] = self::model()->countByAttributes(array(
			'type'=>$this->type,
			'continent_id'=>$this->continent_id,
		), array(
			'condition'=>'continent_rank<' . $this->continent_rank,
		)) + 1;
		$this->_ranks['WR'] = self::model()->countByAttributes(array(
			'type'=>$this->type,
		), array(
			'condition'=>'world_rank<' . $this->world_rank,
		)) + 1;
		return $this->_ranks;
	}

	public function getRank($key) {
		$ranks = $this->getRanks();
		$rank = $ranks[$key];
		if ($rank <= 10) {
			return CHtml::tag('span', array('class'=>'top10'), $rank);
		}
		return $rank;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ranks_sum';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type', 'required'),
			array('country_rank, continent_rank, world_rank', 'numerical', 'integerOnly'=>true),
			array('person_id, type', 'length', 'max'=>10),
			array('country_id, continent_id', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, person_id, country_id, continent_id, type, country_rank, continent_rank, world_rank', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'person_id' => 'Person',
			'country_id' => 'Country',
			'continent_id' => 'Continent',
			'type' => 'Type',
			'country_rank' => 'Country Rank',
			'continent_rank' => 'Continent Rank',
			'world_rank' => 'World Rank',
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('person_id', $this->person_id, true);
		$criteria->compare('country_id', $this->country_id, true);
		$criteria->compare('continent_id', $this->continent_id, true);
		$criteria->compare('type', $this->type, true);
		$criteria->compare('country_rank', $this->country_rank);
		$criteria->compare('continent_rank', $this->continent_rank);
		$criteria->compare('world_rank', $this->world_rank);

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
	 * @return SumOfRanks the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
