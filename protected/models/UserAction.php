<?php

/**
 * This is the model class for table "user_action".
 *
 * The followings are the available columns in table 'user_action':
 * @property string $id
 * @property string $user_id
 * @property string $action
 * @property string $code
 * @property integer $status
 * @property string $date
 */
class UserAction extends ActiveRecord {
	const STATUS_INIT = 0;
	const STATUS_USED = 1;

	public function generateCode() {
		return md5(serialize($this->attributes) . mt_rand());
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user_action';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, action, code, date', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('user_id, date', 'length', 'max'=>11),
			array('action', 'length', 'max'=>20),
			array('code', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, action, code, status, date', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('UserAction', 'ID'),
			'user_id' => Yii::t('UserAction', 'User'),
			'action' => Yii::t('UserAction', 'Action'),
			'code' => Yii::t('UserAction', 'Code'),
			'status' => Yii::t('UserAction', 'Status'),
			'date' => Yii::t('UserAction', 'Date'),
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
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('action',$this->action,true);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('date',$this->date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserAction the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
