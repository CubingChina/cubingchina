<?php

/**
 * This is the model class for table "review".
 *
 * The followings are the available columns in table 'review':
 * @property string $id
 * @property integer $user_id
 * @property string $organizer_id
 * @property string $competition_id
 * @property integer $rank
 * @property string $comments
 * @property string $date
 */
class Review extends ActiveRecord {

	const RANK_GOOD = 2;
	const RANK_NORMAL = 1;
	const RANK_BAD = 0;

	public static function getRanks() {
		return array(
			self::RANK_GOOD=>'好',
			self::RANK_NORMAL=>'中',
			self::RANK_BAD=>'差',
		);
	}

	public function getRankText() {
		$ranks = self::getRanks();
		return isset($ranks[$this->rank]) ? $ranks[$this->rank] : $this->rank;
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
		$buttons[] = CHtml::link('编辑',  array('/board/review/edit',  'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		return implode(' ',  $buttons);
	}

	public function getCommentsButton() {
		if ($this->comments !== '') {
			return CHtml::tag('button', array(
				'class'=>'btn btn-xs btn-square btn-purple view-comments',
				'data-comments'=>$this->comments,
				'data-toggle'=>'modal',
				'data-target'=>'#comments-modal',
			), '查看');
		}
	}

	protected function beforeValidate() {
		$this->handleDate();
		return parent::beforeValidate();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'review';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, date', 'required'),
			array('user_id, rank', 'numerical', 'integerOnly'=>true),
			array('organizer_id, competition_id, date', 'length', 'max'=>10),
			array('comments', 'length', 'max'=>1024),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, organizer_id, competition_id, rank, comments, date', 'safe', 'on'=>'search'),
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
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'organizer'=>array(self::BELONGS_TO, 'User', 'organizer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('Review', 'ID'),
			'user_id'=>Yii::t('Review', 'User'),
			'organizer_id'=>Yii::t('Review', 'Organizer'),
			'competition_id'=>Yii::t('Review', 'Competition'),
			'rank'=>Yii::t('Review', 'Rank'),
			'comments'=>Yii::t('Review', 'Comments'),
			'date'=>Yii::t('Review', 'Date'),
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('organizer_id', $this->organizer_id, true);
		$criteria->compare('competition_id', $this->competition_id, true);
		$criteria->compare('rank', $this->rank);
		$criteria->compare('comments', $this->comments, true);
		$criteria->compare('date', $this->date, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Review the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
