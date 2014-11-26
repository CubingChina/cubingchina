<?php

/**
 * This is the model class for table "news_template".
 *
 * The followings are the available columns in table 'news_template':
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $title_zh
 * @property string $content
 * @property string $content_zh
 */
class NewsTemplate extends ActiveRecord {

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑',  array('/board/news/editTemplate',  'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		return implode(' ',  $buttons);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'news_template';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, title, title_zh, content, content_zh', 'required'),
			array('name', 'length', 'max'=>255),
			array('title, title_zh', 'length', 'max'=>1024),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, title, title_zh, content, content_zh', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('News', 'ID'),
			'name' => Yii::t('News', 'name'),
			'title' => Yii::t('News', 'Title'),
			'title_zh' => Yii::t('News', 'Title Zh'),
			'content' => Yii::t('News', 'Content'),
			'content_zh' => Yii::t('News', 'Content Zh'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('title', $this->title, true);
		$criteria->compare('title_zh', $this->title_zh, true);
		$criteria->compare('content', $this->content, true);
		$criteria->compare('content_zh', $this->content_zh, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return NewsTemplate the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}