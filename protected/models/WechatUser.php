<?php

/**
 * This is the model class for table "wechat_user".
 *
 * The followings are the available columns in table 'wechat_user':
 * @property string $id
 * @property string $nickname
 * @property string $avatar
 * @property string $user_id
 */
class WechatUser extends ActiveRecord {

	public static function getOrCreate($user) {
		if (($wechatUser = self::model()->findByPk($user->id)) === null) {
			$wechatUser = new self();
		}
		$wechatUser->id = $user->id;
		$wechatUser->nickname = $user->nickname;
		$wechatUser->avatar = $user->avatar;
		$wechatUser->save();
		return $wechatUser;
	}

	public function bind($user) {
		$this->user_id = $user_id;
		$this->save();
	}

	public function unbind() {
		$this->user_id = 0;
		$this->save();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'wechat_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			['id, nickname, avatar', 'required'],
			['id', 'length', 'max'=>32],
			['nickname', 'length', 'max'=>128],
			['avatar', 'length', 'max'=>256],
			['user_id', 'length', 'max'=>11],
			['id, nickname, avatar, user_id', 'safe', 'on'=>'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'user'=>[self::BELONGS_TO, 'User', 'user_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return [
			'id'=>'ID',
			'nickname'=>'Nickname',
			'avatar'=>'Avatar',
			'user_id'=>'User',
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

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('nickname', $this->nickname, true);
		$criteria->compare('avatar', $this->avatar, true);
		$criteria->compare('user_id', $this->user_id, true);

		return new CActiveDataProvider($this, [
			'criteria'=>$criteria,
		]);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return WechatUser the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
