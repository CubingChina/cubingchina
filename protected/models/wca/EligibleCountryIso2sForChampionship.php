<?php

/**
 * This is the model class for table "eligible_country_iso2s_for_championship".
 *
 * The followings are the available columns in table 'eligible_country_iso2s_for_championship':
 * @property string $id
 * @property string $championship_type
 * @property string $eligible_country_iso2
 */
class EligibleCountryIso2sForChampionship extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'eligible_country_iso2s_for_championship';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('championship_type, eligible_country_iso2', 'required'),
			array('id', 'length', 'max'=>20),
			array('championship_type, eligible_country_iso2', 'length', 'max'=>191),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, championship_type, eligible_country_iso2', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return [
			'country'=>[self::BELONGS_TO, 'Countries', ['eligible_country_iso2'=>'iso2']],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'championship_type'=>'Championship Type',
			'eligible_country_iso2'=>'Eligible Country Iso2',
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
		$criteria->compare('championship_type',$this->championship_type,true);
		$criteria->compare('eligible_country_iso2',$this->eligible_country_iso2,true);

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
	 * @return EligibleCountryIso2sForChampionship the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
