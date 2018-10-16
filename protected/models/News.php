<?php

/**
 * This is the model class for table "news".
 *
 * The followings are the available columns in table 'news':
 * @property string $id
 * @property integer $user_id
 * @property string $title
 * @property string $title_zh
 * @property string $content
 * @property string $content_zh
 * @property integer $weight
 * @property string $date
 * @property integer $status
 */
class News extends ActiveRecord {

	const STATUS_HIDE = 0;
	const STATUS_SHOW = 1;
	const STATUS_DELETE = 2;

	const WEIGHT_TOP1 = 10;
	const WEIGHT_TOP2 = 5;
	const WEIGHT_NORMAL = 0;

	public static function getAllStatus() {
		return array(
			self::STATUS_HIDE=>'隐藏',
			self::STATUS_SHOW=>'发布',
			// self::STATUS_DELETE=>'删除',
		);
	}

	public static function getWeights() {
		return array(
			self::WEIGHT_NORMAL=>'不置顶',
			self::WEIGHT_TOP1=>'一级置顶',
			self::WEIGHT_TOP2=>'二级置顶',
		);
	}

	public function getWeightText() {
		$weights = self::getWeights();
		return isset($weights[$this->weight]) ? $weights[$this->weight] : $this->weight;
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

	public function getUrl() {
		return CHtml::normalizeUrl(['/post/detail', 'alias'=>$this->alias]);
	}

	public function getDescriptionOrContent() {
		$description = $this->getAttributeValue('description');
		if (trim(strip_tags($description)) !== '') {
			return $description . CHtml::tag('p', ['class'=>'read-more'], CHtml::link(Yii::t('common', 'Read more....'), $this->url));
		}
		return $this->getAttributeValue('content');
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑',  array('/board/news/edit',  'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkPermission('news')) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::link('发布',  array('/board/news/show',  'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::link('隐藏',  array('/board/news/hide',  'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
					break;
			}
		}
		return implode(' ',  $buttons);
	}

	protected function beforeValidate() {
		$this->handleDate();
		return parent::beforeValidate();
	}

	protected function beforeSave() {
		$this->alias = str_replace(' ', '-', $this->title);
		$this->alias = preg_replace('{[^-a-z0-9]}i', '', $this->alias);
		$this->alias = preg_replace('{-+}i', '-', $this->alias);
		return parent::beforeSave();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'news';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return [
			['user_id, title, title_zh, content, content_zh, date', 'required'],
			['user_id, weight, status', 'numerical', 'integerOnly'=>true],
			['title, title_zh', 'length', 'max'=>1024],
			['description_zh, description', 'safe'],
			['id, user_id, title, title_zh, content, content_zh, weight, date, status', 'safe', 'on'=>'search'],
		];
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
			'id' => Yii::t('News', 'ID'),
			'user_id' => Yii::t('News', 'User'),
			'title' => Yii::t('News', 'Title'),
			'title_zh' => Yii::t('News', 'Title Zh'),
			'content' => Yii::t('News', 'Content'),
			'content_zh' => Yii::t('News', 'Content Zh'),
			'weight' => Yii::t('News', 'Weight'),
			'date' => Yii::t('News', 'Date'),
			'status' => Yii::t('News', 'Status'),
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
		$criteria->with = 'user';
		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.user_id',$this->user_id);
		$criteria->compare('t.title',$this->title,true);
		$criteria->compare('t.title_zh',$this->title_zh,true);
		$criteria->compare('t.content',$this->content,true);
		$criteria->compare('t.content_zh',$this->content_zh,true);
		$criteria->compare('t.date',$this->date,true);
		$criteria->compare('t.status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'weight DESC, date DESC',
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
	 * @return News the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
