<?php

/**
 * This is the model class for table "old_competition".
 *
 * The followings are the available columns in table 'old_competition':
 * @property string $id
 * @property string $delegate
 * @property string $organizer
 * @property string $organizer_zh
 */
class OldCompetition extends ActiveRecord {

	public static function generateInfo($info = array()) {
		$string = array();
		foreach ($info as $value) {
			if (!isset($value['email'])) {
				$string[] = "[{$value['name']}]";
			} else {
				$string[] = "[{{$value['name']}}{{$value['email']}}]";
			}
		}
		return implode('', $string);
	}

	public static function formatInfo($info) {
		//[{name}{mail@abc.com}][name]
		if (preg_match_all('{(\[([^\]]+)\])}', $info, $matches)) {
			$info = array();
			foreach ($matches[2] as $value) {
				if (preg_match('{\{([^\}]+)\}\{([^\}]+)\}}', $value, $match)) {
					$info[] = CHtml::mailto('<i class="fa fa-envelope"></i> ' . $match[1], $match[2]);
				} else {
					$info[] = $value;
				}
			}
			return implode(Yii::t('common', ', '), $info);
		} else {
			return $info;
		}
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OldCompetition the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'old_competition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('delegate, organizer, organizer_zh', 'required'),
			array('delegate, organizer, organizer_zh', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, delegate, organizer, organizer_zh', 'safe', 'on'=>'search'),
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
			'id'=>'ID',
			'delegate'=>'Delegate',
			'organizer'=>'Organizer',
			'organizer_zh'=>'Organizer Zh',
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
		$criteria->compare('delegate', $this->delegate, true);
		$criteria->compare('organizer', $this->organizer, true);
		$criteria->compare('organizer_zh', $this->organizer_zh, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}