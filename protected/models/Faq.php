<?php

/**
 * This is the model class for table "faq".
 *
 * The followings are the available columns in table 'faq':
 * @property string $id
 * @property integer $user_id
 * @property string $category_id
 * @property string $title
 * @property string $title_zh
 * @property string $content
 * @property string $content_zh
 * @property string $date
 * @property integer $status
 */
class Faq extends ActiveRecord {

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
		$buttons[] = CHtml::link('编辑',  array('/board/faq/edit',  'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkAccess(User::ROLE_DELEGATE)) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::link('发布',  array('/board/faq/show',  'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::link('隐藏',  array('/board/faq/hide',  'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
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
		return 'faq';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, category_id, title, title_zh, content, content_zh, date', 'required'),
			array('user_id, status', 'numerical', 'integerOnly'=>true),
			array('category_id, date', 'length', 'max'=>10),
			array('title, title_zh', 'length', 'max'=>1024),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, category_id, title, title_zh, content, content_zh, date, status', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Faq', 'ID'),
			'user_id' => Yii::t('Faq', 'User'),
			'category_id' => Yii::t('Faq', 'Category'),
			'title' => Yii::t('Faq', 'Title'),
			'title_zh' => Yii::t('Faq', 'Title Zh'),
			'content' => Yii::t('Faq', 'Content'),
			'content_zh' => Yii::t('Faq', 'Content Zh'),
			'date' => Yii::t('Faq', 'Date'),
			'status' => Yii::t('Faq', 'Status'),
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
		$criteria->compare('category_id',$this->category_id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('title_zh',$this->title_zh,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('content_zh',$this->content_zh,true);
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
	 * @return Faq the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
