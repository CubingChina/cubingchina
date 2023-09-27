<?php

/**
 * This is the model class for table "user_avatar".
 *
 * The followings are the available columns in table 'user_avatar':
 * @property string $id
 * @property string $user_id
 * @property string $md5
 * @property string $extension
 * @property integer $width
 * @property integer $height
 * @property string $add_time
 */
class UserAvatar extends ActiveRecord {

	public function getImg() {
		return CHtml::link(CHtml::image($this->fullUrl, $this->user->getCompetitionName(), array(
			'class'=>'user-avatar',
		)), $this->fullUrl, array(
			'target'=>'_blank',
		));
	}

	public function getFullUrl() {
		return implode('/', array(
			Yii::app()->params->staticUrlPrefix . 'upload',
			$this->md5[0],
			$this->md5 . $this->extension,
		));
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user_avatar';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, md5, extension, width, height', 'required'),
			array('width, height', 'numerical', 'integerOnly'=>true),
			array('user_id, extension, add_time', 'length', 'max'=>10),
			array('md5', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, md5, extension, width, height, add_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('UserAvatar', 'ID'),
			'user_id' => Yii::t('UserAvatar', 'User'),
			'md5' => Yii::t('UserAvatar', 'Md5'),
			'extension' => Yii::t('UserAvatar', 'Extension'),
			'width' => Yii::t('UserAvatar', 'Width'),
			'height' => Yii::t('UserAvatar', 'Height'),
			'add_time' => Yii::t('UserAvatar', 'Add Time'),
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
		$criteria->compare('md5',$this->md5,true);
		$criteria->compare('extension',$this->extension,true);
		$criteria->compare('width',$this->width);
		$criteria->compare('height',$this->height);
		$criteria->compare('add_time',$this->add_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserAvatar the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
