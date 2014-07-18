<?php

/**
 * This is the model class for table "delegate".
 *
 * The followings are the available columns in table 'delegate':
 * @property string $id
 * @property string $name
 * @property string $name_zh
 * @property string $email
 */
class Delegate extends ActiveRecord {
	public static function getDelegates() {
		return self::model()->cache(86400 * 7)->findAll();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'delegate';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, name_zh, email', 'required'),
			array('name, name_zh, email', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, name_zh, email', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Delegate', 'ID'),
			'name' => Yii::t('Delegate', 'Name'),
			'name_zh' => Yii::t('Delegate', 'Name Zh'),
			'email' => Yii::t('Delegate', 'Email'),
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
		$criteria->compare('name_zh',$this->name_zh,true);
		$criteria->compare('email',$this->email,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Delegate the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
