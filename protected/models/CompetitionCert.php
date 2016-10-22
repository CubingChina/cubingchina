<?php

Yii::import('application.cert.*');
/**
 * This is the model class for table "competition_cert".
 *
 * The followings are the available columns in table 'competition_cert':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property string $hash
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class CompetitionCert extends ActiveRecord {
	private $_generator;

	public function getShareIcon() {
		$generator = $this->getGenerator();
		return $generator->getShareIcon() ?: Yii::app()->request->getBaseUrl(true) . '/f/images/icon196.png';
	}

	public function getShareTitle() {
		$generator = $this->getGenerator();
		return $generator->getShareTitle();
	}

	public function getShareDesc() {
		$generator = $this->getGenerator();
		return $generator->getShareDesc();
	}

	public function getImageUrl($type = 'results') {
		return sprintf("%scerts/%s/%s/%s.jpg",
			Yii::app()->params->staticUrlPrefix,
			$this->competition->cert_name,
			$type,
			$this->hash
		);
	}

	public function getUrl() {
		if ($this->hash === '') {
			return $this->competition->getUrl();
		}
		return ['/results/cert', 'hash'=>$this->hash];
	}

	public function getHasCert() {
		return $this->hash != '';
	}

	public function generateCert() {
		$generator = $this->getGenerator();
		if ($generator !== false) {
			$generator->run();
		}
	}

	public function getGenerator() {
		if ($this->_generator !== null) {
			return $this->_generator;
		}
		$competition = $this->competition;
		$certName = $this->competition->cert_name;
		if (class_exists($className = 'Cert' . ucfirst($certName))) {
			return $this->_generator = new $className($this);
		}
		return $this->_generator = false;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competition_cert';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, create_time, update_time', 'length', 'max'=>10),
			array('hash', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, user_id, hash, status, create_time, update_time', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'competition_id' => 'Competition',
			'user_id' => 'User',
			'hash' => 'Hash',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('competition_id', $this->competition_id, true);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('hash', $this->hash, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompetitionCert the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
