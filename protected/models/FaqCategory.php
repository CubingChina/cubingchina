<?php

/**
 * This is the model class for table "faq_category".
 *
 * The followings are the available columns in table 'faq_category':
 * @property string $id
 * @property integer $user_id
 * @property string $name
 * @property string $name_zh
 * @property string $date
 * @property integer $status
 */
class FaqCategory extends ActiveRecord {

	const STATUS_HIDE = 0;
	const STATUS_SHOW = 1;
	const STATUS_DELETE = 2;

	public static function getAllStatus() {
		return array(
			self::STATUS_HIDE=>'隐藏', 
			self::STATUS_SHOW=>'发布', 
			// self::STATUS_DELETE=>'删除', 
		);
	}

	public static function getCategories() {
		$categories = self::model()->findAll(array(
			'order'=>'date DESC',
		));
		return CHtml::listData($categories, 'id', 'name_zh');
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function handleDate() {
		if (trim($this->date) != '') {
			$date = strtotime($this->date);
			if ($date !== false) {
				$this->date = $date;
			} else {
				$this->date = 0;
			}
		} else {
			$this->date = 0;
		}
	}

	public function formatDate() {
		if (!empty($this->date)) {
			$this->date = date('Y-m-d H:i:s',  $this->date);
		} else {
			$this->date = '';
		}
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑',  array('/board/faq/editCategory',  'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkAccess(User::ROLE_DELEGATE)) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::link('发布',  array('/board/faq/showCategory',  'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::link('隐藏',  array('/board/faq/hideCategory',  'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
					break;
			}
		}
		return implode(' ',  $buttons);
	}

	protected function beforeValidate() {
		$this->handleDate();
		return parent::beforeValidate();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'faq_category';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, name, name_zh, date', 'required'),
			array('user_id, status', 'numerical', 'integerOnly'=>true),
			array('name, name_zh', 'length', 'max'=>128),
			array('date', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, name, name_zh, date, status', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('FaqCategory', 'ID'),
			'user_id' => Yii::t('FaqCategory', 'User'),
			'name' => Yii::t('FaqCategory', 'Name'),
			'name_zh' => Yii::t('FaqCategory', 'Name Zh'),
			'date' => Yii::t('FaqCategory', 'Date'),
			'status' => Yii::t('FaqCategory', 'Status'),
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('name_zh',$this->name_zh,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'date DESC',
			),
			'pagination'=>array(
				'pageVar'=>'page',
			),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FaqCategory the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
