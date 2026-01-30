<?php

/**
 * This is the model class for table "result_attempts".
 *
 * The followings are the available columns in table 'result_attempts':
 * @property integer $value
 * @property integer $attempt_number
 * @property integer $result_id
 */
class ResultAttempts extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'result_attempts';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('value, attempt_number, result_id', 'required'),
			array('value, result_id', 'numerical', 'integerOnly'=>true),
			array('attempt_number', 'numerical', 'integerOnly'=>true, 'min'=>1, 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('value, attempt_number, result_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'result'=>array(self::BELONGS_TO, 'Results', 'result_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'value' => Yii::t('result_attempts', 'Value'),
			'attempt_number' => Yii::t('result_attempts', 'Attempt Number'),
			'result_id' => Yii::t('result_attempts', 'Result'),
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

		$criteria->compare('value',$this->value);
		$criteria->compare('attempt_number',$this->attempt_number);
		$criteria->compare('result_id',$this->result_id);

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
	 * @return ResultAttempt the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
