<?php

/**
 * This is the model class for table "concise_average_results".
 *
 * The followings are the available columns in table 'concise_average_results':
 * @property string $id
 * @property integer $average
 * @property string $value_and_id
 * @property string $person_id
 * @property string $event_id
 * @property string $country_id
 * @property string $continent_id
 * @property integer $year
 * @property integer $month
 * @property integer $day
 */
class ConciseAverageResults extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'concise_average_results';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('average, year, month, day', 'numerical', 'integerOnly'=>true),
			array('id, person_id', 'length', 'max'=>10),
			array('value_and_id', 'length', 'max'=>21),
			array('event_id', 'length', 'max'=>6),
			array('country_id, continent_id', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, average, value_and_id, person_id, event_id, country_id, continent_id, year, month, day', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('concise_average_results', 'ID'),
			'average' => Yii::t('concise_average_results', 'Average'),
			'value_and_id' => Yii::t('concise_average_results', 'Value And'),
			'person_id' => Yii::t('concise_average_results', 'Person'),
			'event_id' => Yii::t('concise_average_results', 'Event'),
			'country_id' => Yii::t('concise_average_results', 'Country'),
			'continent_id' => Yii::t('concise_average_results', 'Continent'),
			'year' => Yii::t('concise_average_results', 'Year'),
			'month' => Yii::t('concise_average_results', 'Month'),
			'day' => Yii::t('concise_average_results', 'Day'),
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
		$criteria->compare('average',$this->average);
		$criteria->compare('value_and_id',$this->value_and_id,true);
		$criteria->compare('person_id',$this->person_id,true);
		$criteria->compare('event_id',$this->event_id,true);
		$criteria->compare('country_id',$this->country_id,true);
		$criteria->compare('continent_id',$this->continent_id,true);
		$criteria->compare('year',$this->year);
		$criteria->compare('month',$this->month);
		$criteria->compare('day',$this->day);

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
	 * @return concise_average_results the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
