<?php

/**
 * This is the model class for table "competition_organizer".
 *
 * The followings are the available columns in table 'competition_organizer':
 * @property string $id
 * @property string $competition_id
 * @property string $organizer_id
 */
class CompetitionOrganizer extends ActiveRecord {

	public function __toJson() {
		return $this->user;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competition_organizer';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, organizer_id', 'required'),
			array('competition_id, organizer_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, organizer_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'organizer_id'),
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('CompetitionOrganizer', 'ID'),
			'competition_id' => Yii::t('CompetitionOrganizer', 'Competition'),
			'organizer_id' => Yii::t('CompetitionOrganizer', 'Organizer'),
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
		$criteria->compare('competition_id',$this->competition_id,true);
		$criteria->compare('organizer_id',$this->organizer_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompetitionOrganizer the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
