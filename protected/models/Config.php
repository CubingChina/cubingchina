<?php

/**
 * This is the model class for table "config".
 *
 * The followings are the available columns in table 'config':
 * @property string $id
 * @property string $title
 * @property string $title_zh
 * @property string $content
 * @property string $content_zh
 * @property integer $status
 */
class Config extends ActiveRecord {

	const STATUS_HIDE = 0;
	const STATUS_SHOW = 1;

	private static $_configs = [];

	public static function getAllStatus() {
		return array(
			self::STATUS_HIDE=>'隐藏', 
			self::STATUS_SHOW=>'发布', 
			// self::STATUS_DELETE=>'删除', 
		);
	}

	public static function getConfig($id) {
		if (!isset(self::$_configs[$id])) {
			self::$_configs[$id] = self::model()->findByPk($id);
		}
		return self::$_configs[$id];
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑', array('/board/config/edit', 'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR)) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::link('发布', array('/board/config/show', 'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::link('隐藏', array('/board/config/hide', 'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
					break;
			}
		}
		return implode(' ', $buttons);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'config';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>20),
			array('title, title_zh', 'length', 'max'=>1024),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, title_zh, content, content_zh, status', 'safe'),
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
			'title' => 'Title',
			'title_zh' => 'Title Zh',
			'content' => 'Content',
			'content_zh' => 'Content Zh',
			'status' => 'Status',
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

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('title_zh',$this->title_zh,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('content_zh',$this->content_zh,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Config the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}
