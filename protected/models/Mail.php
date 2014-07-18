<?php

/**
 * This is the model class for table "mail".
 *
 * The followings are the available columns in table 'mail':
 * @property string $id
 * @property string $to
 * @property string $subject
 * @property string $message
 * @property integer $sent
 * @property integer $add_time
 * @property integer $update_time
 * @property integer $sent_time
 */
class Mail extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'mail';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('to, subject, message, add_time, update_time', 'required'),
			array('sent, add_time, update_time, sent_time', 'numerical', 'integerOnly'=>true),
			array('to', 'length', 'max'=>128),
			array('subject', 'length', 'max'=>256),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, to, subject, message, sent, add_time, update_time, sent_time', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Mail', 'ID'),
			'to' => Yii::t('Mail', 'To'),
			'subject' => Yii::t('Mail', 'Subject'),
			'message' => Yii::t('Mail', 'Message'),
			'sent' => Yii::t('Mail', 'Sent'),
			'add_time' => Yii::t('Mail', 'Add Time'),
			'update_time' => Yii::t('Mail', 'Update Time'),
			'sent_time' => Yii::t('Mail', 'Sent Time'),
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
		$criteria->compare('to',$this->to,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('sent',$this->sent);
		$criteria->compare('add_time',$this->add_time);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('sent_time',$this->sent_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Mail the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
