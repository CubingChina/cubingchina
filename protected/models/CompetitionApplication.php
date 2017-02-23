<?php

/**
 * This is the model class for table "competition_application".
 *
 * The followings are the available columns in table 'competition_application':
 * @property string $id
 * @property string $competition_id
 * @property string $schedule
 * @property string $organized_competition
 * @property string $self_introduction
 * @property string $team_introduction
 * @property string $venue_detail
 * @property string $budget
 * @property string $sponsor
 * @property string $other
 * @property string $create_time
 * @property string $update_time
 */
class CompetitionApplication extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competition_application';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return [
			['competition_id, create_time', 'required'],
			['competition_id, create_time, update_time', 'length', 'max'=>11],
			['schedule, organized_competition, self_introduction, team_introduction, venue_detail, budget, sponsor, other', 'safe'],
			['id, competition_id, organized_competition, self_introduction, team_introduction, venue_detail, budget, sponsor, other, create_time, update_time', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			[self::BELONGS_TO, 'Competition', 'competition_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'competition_id' => 'Competition',
			'schedule' => 'Schedule',
			'organized_competition' => 'Organized Competition',
			'self_introduction' => 'Self Introduction',
			'team_introduction' => 'Team Introduction',
			'venue_detail' => 'Venue Detail',
			'budget' => 'Budget',
			'sponsor' => 'Sponsor',
			'other' => 'Other',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		];
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

		$criteria->compare('id', $this->id,true);
		$criteria->compare('competition_id', $this->competition_id,true);
		$criteria->compare('schedule', $this->schedule,true);
		$criteria->compare('organized_competition', $this->organized_competition,true);
		$criteria->compare('self_introduction', $this->self_introduction,true);
		$criteria->compare('team_introduction', $this->team_introduction,true);
		$criteria->compare('venue_detail', $this->venue_detail,true);
		$criteria->compare('budget', $this->budget,true);
		$criteria->compare('sponsor', $this->sponsor,true);
		$criteria->compare('other', $this->other,true);
		$criteria->compare('create_time', $this->create_time,true);
		$criteria->compare('update_time', $this->update_time,true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompetitionApplication the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
