<?php

/**
 * This is the model class for table "SumOfRanks".
 *
 * The followings are the available columns in table 'SumOfRanks':
 * @property string $id
 * @property string $personId
 * @property string $countryId
 * @property string $continentId
 * @property string $type
 * @property integer $countryRank
 * @property integer $continentRank
 * @property integer $worldRank
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
			'countryId'=>$this->countryId,
		), array(
			'condition'=>'countryRank<' . $this->countryRank,
		)) + 1;
		$this->_ranks['CR'] = self::model()->countByAttributes(array(
			'type'=>$this->type,
			'continentId'=>$this->continentId,
		), array(
			'condition'=>'continentRank<' . $this->continentRank,
		)) + 1;
		$this->_ranks['WR'] = self::model()->countByAttributes(array(
			'type'=>$this->type,
		), array(
			'condition'=>'worldRank<' . $this->worldRank,
		)) + 1;
		return $this->_ranks;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'RanksSum';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type', 'required'),
			array('countryRank, continentRank, worldRank', 'numerical', 'integerOnly'=>true),
			array('personId, type', 'length', 'max'=>10),
			array('countryId, continentId', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, personId, countryId, continentId, type, countryRank, continentRank, worldRank', 'safe', 'on'=>'search'),
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
			'personId' => 'Person',
			'countryId' => 'Country',
			'continentId' => 'Continent',
			'type' => 'Type',
			'countryRank' => 'Country Rank',
			'continentRank' => 'Continent Rank',
			'worldRank' => 'World Rank',
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
		$criteria->compare('personId', $this->personId, true);
		$criteria->compare('countryId', $this->countryId, true);
		$criteria->compare('continentId', $this->continentId, true);
		$criteria->compare('type', $this->type, true);
		$criteria->compare('countryRank', $this->countryRank);
		$criteria->compare('continentRank', $this->continentRank);
		$criteria->compare('worldRank', $this->worldRank);

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
