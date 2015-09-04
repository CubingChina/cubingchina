<?php

/**
 * This is the model class for table "user_permission".
 *
 * The followings are the available columns in table 'user_permission':
 * @property string $id
 * @property string $user_id
 * @property string $permission
 */
class UserPermission extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user_permission';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, permission', 'required'),
			array('user_id', 'length', 'max'=>10),
			array('permission', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, permission', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('UserPermission', 'ID'),
			'user_id' => Yii::t('UserPermission', 'User'),
			'permission' => Yii::t('UserPermission', 'Permission'),
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
		$criteria->compare('permission',$this->permission,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserPermission the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
